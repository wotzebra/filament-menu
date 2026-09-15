<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;

uses(RefreshDatabase::class);

beforeEach(function () {
    LocaleCollection::add(new Locale('nl'));
    LocaleCollection::add(new Locale('fr'));

    $this->migration = include __DIR__ . '/../database/migrations/2026_09_15_100000_store_menu_item_translations_locale_first.php';

    $menuId = DB::table('menus')->insertGetId([
        'working_title' => 'Main',
        'identifier' => 'main',
        'depth' => 1,
    ]);

    $this->insertItem = fn (?array $data) => DB::table('menu_items')->insertGetId([
        'menu_id' => $menuId,
        'working_title' => 'Home',
        'type' => 'link-picker',
        'data' => $data === null ? null : json_encode($data),
    ]);

    $this->itemData = fn (int $id) => json_decode(DB::table('menu_items')->find($id)->data, true);
});

it('moves field-first items back to locale-first', function () {
    $id = ($this->insertItem)([
        'link' => ['route' => 'home', 'newTab' => false],
        'label' => ['nl' => 'Home', 'fr' => 'Accueil'],
        'online' => ['nl' => true, 'fr' => false],
    ]);

    $this->migration->up();

    expect(($this->itemData)($id))->toEqual([
        'link' => ['route' => 'home', 'newTab' => false],
        'nl' => ['label' => 'Home', 'online' => true],
        'fr' => ['label' => 'Accueil', 'online' => false],
    ]);
});

it('leaves locale-first items and items without data untouched', function () {
    $data = [
        'link' => ['route' => 'home', 'newTab' => false],
        'nl' => ['label' => 'Home', 'online' => true],
    ];

    $localeFirstId = ($this->insertItem)($data);
    $emptyId = ($this->insertItem)(null);

    $this->migration->up();

    expect(($this->itemData)($localeFirstId))->toBe($data)
        ->and(DB::table('menu_items')->find($emptyId)->data)->toBeNull();
});

<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;
use Wotz\LocaleCollection\LocaleCollection as LocaleCollectionInstance;

uses(RefreshDatabase::class);

beforeEach(function () {
    LocaleCollection::add(new Locale('nl'));
    LocaleCollection::add(new Locale('fr'));

    $this->migration = include __DIR__ . '/../database/migrations/2026_09_15_000000_store_menu_item_translations_field_first.php';

    $this->menuId = DB::table('menus')->insertGetId([
        'working_title' => 'Main',
        'identifier' => 'main',
        'depth' => 1,
    ]);

    $this->insertItem = fn (?array $data) => DB::table('menu_items')->insertGetId([
        'menu_id' => $this->menuId,
        'working_title' => 'Home',
        'type' => 'link-picker',
        'data' => $data === null ? null : json_encode($data),
    ]);

    $this->itemData = fn (int $id) => json_decode(DB::table('menu_items')->find($id)->data, true);
});

$localeFirst = [
    'link' => ['route' => 'home', 'parameters' => [], 'newTab' => false],
    'nl' => ['label' => 'Home', 'online' => true, 'translated_link' => null],
    'fr' => ['label' => 'Accueil', 'online' => false, 'translated_link' => ['route' => 'home', 'newTab' => true]],
];

$fieldFirst = [
    'link' => ['route' => 'home', 'parameters' => [], 'newTab' => false],
    'label' => ['nl' => 'Home', 'fr' => 'Accueil'],
    'online' => ['nl' => true, 'fr' => false],
    'translated_link' => ['nl' => null, 'fr' => ['route' => 'home', 'newTab' => true]],
];

it('moves locale-first translations to field-first', function () use ($localeFirst, $fieldFirst) {
    $id = ($this->insertItem)($localeFirst);

    $this->migration->up();

    expect(($this->itemData)($id))->toEqual($fieldFirst);
});

it('leaves items that are already field-first untouched', function () use ($fieldFirst) {
    $id = ($this->insertItem)($fieldFirst);

    $this->migration->up();
    $this->migration->up();

    expect(($this->itemData)($id))->toBe($fieldFirst);
});

it('skips items without data', function () {
    $id = ($this->insertItem)(null);

    $this->migration->up();

    expect(DB::table('menu_items')->find($id)->data)->toBeNull();
});

it('moves field-first translations back to locale-first on rollback', function () use ($localeFirst) {
    $id = ($this->insertItem)($localeFirst);

    $this->migration->up();
    $this->migration->down();

    expect(($this->itemData)($id))->toEqual($localeFirst);
});

it('refuses to run without registered locales', function () use ($localeFirst) {
    ($this->insertItem)($localeFirst);

    LocaleCollection::swap(new LocaleCollectionInstance);

    $this->migration->up();
})->throws(RuntimeException::class);

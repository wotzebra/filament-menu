<?php

use Livewire\Livewire;
use Wotz\FilamentMenu\Tests\Fixtures\MenuItemForm;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;
use Wotz\TranslatableTabs\Forms\TranslatableTabs;

beforeEach(function () {
    LocaleCollection::add(new Locale('nl'));
    LocaleCollection::add(new Locale('fr'));

    TranslatableTabs::configureUsing(fn (TranslatableTabs $tabs) => $tabs->locales(['nl', 'fr']));
});

it('opens a saved menu item with its translations in the locale tabs', function () {
    Livewire::test(MenuItemForm::class, [
        'itemData' => [
            'label' => ['nl' => 'Home', 'fr' => 'Accueil'],
            'online' => ['nl' => true, 'fr' => false],
        ],
    ])
        ->assertSet('data.nl.label', 'Home')
        ->assertSet('data.fr.label', 'Accueil')
        ->assertSet('data.nl.online', true);
});

it('keeps a menu item intact over repeated saves', function () {
    $data = [
        'label' => ['nl' => 'Home', 'fr' => 'Accueil'],
        'translated_link' => ['nl' => null, 'fr' => null],
        'online' => ['nl' => true, 'fr' => false],
        'link' => null,
    ];

    foreach (range(1, 2) as $_) {
        $data = Livewire::test(MenuItemForm::class, ['itemData' => $data])
            ->instance()
            ->form
            ->getState();
    }

    expect($data['label'])->toBe(['nl' => 'Home', 'fr' => 'Accueil'])
        ->and($data['online'])->toBe(['nl' => true, 'fr' => false])
        ->and($data)->not->toHaveKeys(['nl', 'fr']);
});

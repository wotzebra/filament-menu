<?php

use Wotz\FilamentMenu\Models\MenuItem;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;

beforeEach(function () {
    LocaleCollection::add(new Locale('nl'));
    LocaleCollection::add(new Locale('fr'));
});

it('moves field-first translations under their locale', function () {
    expect(MenuItem::localeFirst([
        'link' => ['route' => 'home', 'parameters' => [], 'newTab' => false],
        'label' => ['nl' => 'Home', 'fr' => 'Accueil'],
        'translated_link' => ['nl' => null, 'fr' => ['route' => 'home', 'newTab' => true]],
        'online' => ['nl' => true, 'fr' => false],
    ]))->toEqual([
        'link' => ['route' => 'home', 'parameters' => [], 'newTab' => false],
        'nl' => ['label' => 'Home', 'translated_link' => null, 'online' => true],
        'fr' => ['label' => 'Accueil', 'translated_link' => ['route' => 'home', 'newTab' => true], 'online' => false],
    ]);
});

it('leaves locale-first data unchanged', function () {
    $data = [
        'link' => ['route' => 'home', 'newTab' => false],
        'nl' => ['label' => 'Home', 'online' => true],
        'fr' => ['label' => 'Accueil', 'online' => false],
    ];

    expect(MenuItem::localeFirst($data))->toBe($data);
});

it('leaves arrays that are not keyed by locales alone', function () {
    $data = [
        'nl' => ['label' => 'Home', 'online' => true],
        'tags' => ['one', 'two'],
        'meta' => ['nl' => 'x', 'color' => 'red'],
        'empty' => [],
    ];

    expect(MenuItem::localeFirst($data))->toBe($data);
});

it('takes the locales from the online flag when they are not registered', function () {
    LocaleCollection::swap(new Wotz\LocaleCollection\LocaleCollection);

    expect(MenuItem::localeFirst([
        'label' => ['nl' => 'Home', 'de' => 'Startseite'],
        'online' => ['nl' => true, 'de' => true],
    ]))->toEqual([
        'nl' => ['label' => 'Home', 'online' => true],
        'de' => ['label' => 'Startseite', 'online' => true],
    ]);
});

<?php

use Illuminate\View\View;
use Wotz\FilamentMenu\NavigationElements\LinkPickerElement;
use Wotz\FilamentMenu\NavigationElements\NavigationElement;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;

beforeEach(function () {
    LocaleCollection::add(new Locale('nl'));
    LocaleCollection::add(new Locale('fr'));

    $this->element = new class extends NavigationElement
    {
        public static string $name = 'Test';

        public function render(array $data): ?View
        {
            return null;
        }

        public function schema(): array
        {
            return [];
        }
    };

    $this->data = [
        'label' => ['nl' => 'Home', 'fr' => 'Accueil'],
        'online' => ['nl' => true, 'fr' => false],
    ];
});

it('reads the title for the current locale', function () {
    app()->setLocale('nl');
    expect($this->element->title($this->data))->toBe('Home');

    app()->setLocale('fr');
    expect($this->element->title($this->data))->toBe('Accueil');
});

it('reads the online flag for the current locale', function () {
    app()->setLocale('nl');
    expect($this->element->shown($this->data))->toBeTrue();

    app()->setLocale('fr');
    expect($this->element->shown($this->data))->toBeFalse();
});

it('reports the online flag per locale', function () {
    expect($this->element->locales($this->data))->toBe(['nl' => true, 'fr' => false]);
});

it('falls back to an empty title and a hidden item when a locale is missing', function () {
    app()->setLocale('de');

    expect($this->element->title($this->data))->toBe('')
        ->and($this->element->shown($this->data))->toBeFalse();
});

it('prefers the translated link and its new tab flag over the default link', function () {
    app()->setLocale('nl');

    $element = new LinkPickerElement;
    $data = [
        'link' => ['route' => 'home', 'newTab' => false],
        'translated_link' => ['nl' => ['route' => 'home', 'newTab' => true], 'fr' => null],
    ];

    expect($element->hasTargetBlank($data))->toBeTrue();

    app()->setLocale('fr');

    expect($element->hasTargetBlank($data))->toBeFalse();
});

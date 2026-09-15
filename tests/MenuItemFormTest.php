<?php

use Livewire\Livewire;
use Wotz\FilamentMenu\Models\MenuItem;
use Wotz\FilamentMenu\NavigationElements\LinkPickerElement;
use Wotz\FilamentMenu\Tests\Fixtures\MenuItemForm;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;
use Wotz\TranslatableTabs\Forms\TranslatableTabs;

beforeEach(function () {
    LocaleCollection::add(new Locale('nl'));
    LocaleCollection::add(new Locale('fr'));

    TranslatableTabs::configureUsing(fn (TranslatableTabs $tabs) => $tabs->locales(['nl', 'fr']));
});

/** Open the item in the form and save it, the way `MenuBuilder::formAction()` does. */
function saveMenuItem(array $data): array
{
    return MenuItem::localeFirst(
        Livewire::test(MenuItemForm::class, ['itemData' => $data])->instance()->form->getState()
    );
}

it('opens a menu item with its translations in the locale tabs', function () {
    Livewire::test(MenuItemForm::class, [
        'itemData' => [
            'nl' => ['label' => 'Home', 'online' => true],
            'fr' => ['label' => 'Accueil', 'online' => false],
        ],
    ])
        ->assertSet('data.nl.label', 'Home')
        ->assertSet('data.fr.label', 'Accueil')
        ->assertSet('data.nl.online', true);
});

it('keeps a menu item intact over repeated saves', function () {
    $data = [
        'link' => null,
        'nl' => ['label' => 'Home', 'translated_link' => null, 'online' => true],
        'fr' => ['label' => 'Accueil', 'translated_link' => null, 'online' => false],
    ];

    $saved = saveMenuItem(saveMenuItem($data));

    expect($saved)->toEqual($data);

    $element = new LinkPickerElement;

    app()->setLocale('nl');
    expect($element->title($saved))->toBe('Home')
        ->and($element->shown($saved))->toBeTrue();

    app()->setLocale('fr');
    expect($element->title($saved))->toBe('Accueil')
        ->and($element->shown($saved))->toBeFalse();
});

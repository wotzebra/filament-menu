<?php

use Spatie\Navigation\Helpers\ActiveUrlChecker;
use Wotz\FilamentMenu\NavigationElements;

return [
    'navigation-elements' => [
        'link-picker' => NavigationElements\LinkPickerElement::class,
    ],

    'active-url-checker' => ActiveUrlChecker::class,
];

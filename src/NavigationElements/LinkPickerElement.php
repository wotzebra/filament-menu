<?php

namespace Wotz\FilamentMenu\NavigationElements;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Wotz\LinkPicker\Filament\LinkPickerInput;
use Wotz\TranslatableTabs\Forms\TranslatableTabs;

class LinkPickerElement extends NavigationElement
{
    public static string $name = 'Normal link';

    public function render(array $data): ?View
    {
        $active = $data['active'] ?? false;
        $link = $data['attributes']['data'];

        return view('filament-menu::components.navigation-elements.link-picker-element', [
            'active' => $active,
            'label' => $this->title($link),
            'link' => $this->link($link),
            'children' => $data['children'] ?? [],
        ]);
    }

    public function link(array $data): string|HtmlString
    {
        return lroute($this->translation($data, 'translated_link') ?? $data['link'] ?? '') ?? '';
    }

    public function hasTargetBlank(array $data): bool
    {
        $link = $this->translation($data, 'translated_link') ?? $data['link'] ?? [];

        return $link['newTab'] ?? false;
    }

    public function schema(): array
    {
        return [
            TranslatableTabs::make()
                ->columnSpan(['lg' => 2])
                ->defaultFields([
                    LinkPickerInput::make('link'),
                ])
                ->translatableFields(fn () => [
                    TextInput::make('label')
                        ->label(__('filament-menu::admin.label'))
                        ->required(fn (Get $get) => $get('online')),

                    LinkPickerInput::make('translated_link')
                        ->label(__('filament-menu::admin.translated link'))
                        ->helperText(__('filament-menu::admin.override translation link')),

                    Checkbox::make('online'), // TODO: Toggle doesn't work on create ?
                ]),
        ];
    }
}

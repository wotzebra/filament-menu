<?php

namespace Wotz\FilamentMenu\Tests\Fixtures;

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;
use Wotz\FilamentMenu\NavigationElements\LinkPickerElement;

/**
 * The menu item form as `MenuBuilder::formAction()` builds it: the element's
 * schema filled with the item's JSON `data`, without a translatable model.
 */
class MenuItemForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public array $itemData = [];

    public function mount(): void
    {
        $this->form->fill($this->itemData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(LinkPickerElement::make()->schema())
            ->statePath('data');
    }

    public function render(): string
    {
        return '<div>{{ $this->form }}</div>';
    }
}

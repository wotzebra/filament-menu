<?php

namespace Wotz\FilamentMenu\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Wotz\FilamentMenu\Filament\Resources\MenuResource;
use Wotz\FilamentMenu\Models\Menu;
use Wotz\FilamentMenu\Models\MenuItem;

class MenuBuilder extends Page
{
    use Concerns\InteractsWithRecord;

    protected static string $resource = MenuResource::class;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament-menu::filament.pages.menu-builder';

    protected $listeners = [
        'refresh' => '$refresh',
    ];

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function addAction(): Action
    {
        return $this->formAction()
            ->label(__('filament-menu::menu-builder.add menu item'))
            ->size('sm')
            ->button();
    }

    public function editAction(): Action
    {
        return $this->formAction()
            ->icon('heroicon-o-pencil')
            ->label(__('filament-menu::menu-builder.edit menu item'))
            ->size('sm')
            ->link();
    }

    public function formAction(): Action
    {
        $types = collect(config('filament-menu.navigation-elements', []))
            ->mapWithKeys(fn (string $element) => [$element => $element::name()]);

        return Action::make('edit')
            ->fillForm(function (array $arguments) {
                $menuItem = isset($arguments['menuItem'])
                    ? MenuItem::find($arguments['menuItem'])
                    : new MenuItem;

                return [
                    'working_title' => $menuItem->working_title,
                    'type' => $menuItem->type,
                    ...$menuItem->data ?? [],
                ];
            })
            ->schema(fn () => [
                TextInput::make('working_title')
                    ->label(__('filament-menu::admin.working title'))
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->label(__('filament-menu::admin.type'))
                    ->options($types)
                    ->required()
                    ->reactive(),

                Grid::make(1)
                    ->hidden(fn (Get $get) => empty($get('type')))
                    ->schema(fn (Get $get) => $get('type') ? $get('type')::make()->schema() : []),
            ])
            ->action(function (array $arguments, array $data) {
                $menuItem = MenuItem::updateOrCreate([
                    'id' => $arguments['menuItem'] ?? null,
                    'menu_id' => $this->record->id,
                ], [
                    'working_title' => $data['working_title'],
                    'type' => $data['type'],
                    'data' => collect($data)->except('type', 'working_title'),
                ]);

                $title = $menuItem->wasRecentlyCreated
                    ? __('filament-menu::menu-builder.successfully created')
                    : __('filament-menu::menu-builder.successfully updated');

                Notification::make()
                    ->title($title)
                    ->success()
                    ->send();

                $this->record->refresh();
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->icon('heroicon-o-trash')
            ->label(__('filament-menu::menu-builder.delete menu item'))
            ->size('sm')
            ->color('danger')
            ->link()
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $menuItem = MenuItem::findOrFail($arguments['menuItem'] ?? null);

                MenuItem::where('parent_id', $menuItem->id)->update([
                    'parent_id' => null,
                ]);

                $menuItem->delete();

                Notification::make()
                    ->title(__('filament-menu::menu-item.deleted'))
                    ->success()
                    ->send();

                $this->record->refresh();
            });
    }

    public function handleNewOrder(string $statePath, array $items)
    {
        $itemIds = collect($items)->map(fn ($item) => Str::afterLast($item, '.'));

        MenuItem::whereIn('id', $itemIds)->update([
            'parent_id' => ($statePath === 'data.items') ? null : Str::afterLast($statePath, '.'),
        ]);

        MenuItem::setNewOrder($itemIds, 1000);

        Notification::make()
            ->title(__('filament-menu::menu-builder.successfully sorted'))
            ->success()
            ->send();

        $this->record->refresh();
    }

    public static function getResource(): string
    {
        return static::$resource;
    }

    public function getModel(): string
    {
        return MenuItem::class;
    }

    protected function resolveRecord($key): Menu
    {
        $record = static::getResource()::resolveRecordRouteBinding($key);

        if ($record === null) {
            throw (new ModelNotFoundException)->setModel(Menu::class, [$key]);
        }

        return $record;
    }

    protected function mutateData(array $data): array
    {
        $model = app($this->getModel());
        foreach (Arr::except($data, $model->getFillable()) as $locale => $values) {
            if (! is_array($values)) {
                continue;
            }

            foreach (Arr::only($values, $model->getTranslatableAttributes()) as $key => $value) {
                $data[$key][$locale] = $value;
            }
        }

        return $data;
    }
}

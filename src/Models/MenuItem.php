<?php

namespace Wotz\FilamentMenu\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\SortableTrait;
use Wotz\FilamentMenu\NavigationElements\NavigationElement;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;

/**
 * @property string $working_title
 * @property string|array $link
 * @property string|array $translated_link
 * @property string $label
 * @property int $parent_id
 * @property int $menu_id
 * @property bool $online
 * @property class-string<NavigationElement>|null $type
 * @property array|null $data
 */
class MenuItem extends Model
{
    use SortableTrait;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'sort_order',
        'working_title',
        'type',
        'data',
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    public $with = [
        'children',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->orderBy('sort_order');
    }

    public function type(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => config("filament-menu.navigation-elements.{$value}"),
            set: fn (?string $value) => array_search($value, config('filament-menu.navigation-elements')),
        );
    }

    public function onlineValues(): array
    {
        return (new $this->type)->locales($this->data);
    }

    /**
     * Menu item data stores translations locale-first (`nl.label`), which is
     * what the navigation elements read. wotz/filament-translatable-tabs v3
     * dehydrates them field-first (`label.nl`), so move every value keyed by
     * locales back under its locale. Locale-first data is returned unchanged.
     */
    public static function localeFirst(array $data): array
    {
        $locales = LocaleCollection::map(fn (Locale $locale) => $locale->locale())
            // Every element has a per-locale online flag, so its keys name the
            // locales even when the LocaleCollection is not filled yet.
            ->merge(is_array($data['online'] ?? null) ? array_keys($data['online']) : [])
            ->unique()
            ->all();

        foreach ($data as $field => $translations) {
            if (
                in_array($field, $locales, true)
                || ! is_array($translations)
                || $translations === []
                || array_diff(array_keys($translations), $locales) !== []
            ) {
                continue;
            }

            foreach ($translations as $locale => $value) {
                $data[$locale][$field] = $value;
            }

            unset($data[$field]);
        }

        return $data;
    }
}

<?php

namespace Wotz\FilamentMenu\NavigationElements;

use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;

/** @phpstan-consistent-constructor */
abstract class NavigationElement
{
    public static string $name;

    abstract public function render(array $data): ?View;

    abstract public function schema(): array;

    public function link(array $data): string|HtmlString
    {
        return '#';
    }

    public function hasTargetBlank(array $data): bool
    {
        return false;
    }

    public function title(array $data): string
    {
        return $this->translation($data, 'label') ?? '';
    }

    public function shown(array $data): bool
    {
        return (bool) $this->translation($data, 'online');
    }

    public function locales(array $data): array
    {
        return LocaleCollection::mapWithKeys(fn (Locale $locale) => [
            $locale->locale() => (bool) $this->translation($data, 'online', $locale->locale()),
        ])->toArray();
    }

    /**
     * A translated value from a menu item's data, which stores translations
     * field-first: `['label' => ['nl' => 'Home'], 'online' => ['nl' => true]]`.
     */
    protected function translation(array $data, string $field, ?string $locale = null): mixed
    {
        return $data[$field][$locale ?? app()->getLocale()] ?? null;
    }

    public static function make(): static
    {
        return new static;
    }

    public static function name(): string
    {
        return static::$name;
    }
}

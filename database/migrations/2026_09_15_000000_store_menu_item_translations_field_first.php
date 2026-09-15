<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;

return new class extends Migration
{
    /**
     * wotz/filament-translatable-tabs v3 stores translations field-first
     * (`label.nl`) where v2 stored them locale-first (`nl.label`). Move the
     * existing menu item data to the field-first shape.
     */
    public function up(): void
    {
        $this->transformData(function (array $data, array $locales): array {
            foreach ($locales as $locale) {
                if (! is_array($data[$locale] ?? null)) {
                    continue;
                }

                foreach ($data[$locale] as $field => $value) {
                    $data[$field][$locale] = $value;
                }

                unset($data[$locale]);
            }

            return $data;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->transformData(function (array $data, array $locales): array {
            foreach ($data as $field => $translations) {
                if (! is_array($translations) || $translations === [] || array_diff(array_keys($translations), $locales) !== []) {
                    continue;
                }

                foreach ($translations as $locale => $value) {
                    $data[$locale][$field] = $value;
                }

                unset($data[$field]);
            }

            return $data;
        });
    }

    /**
     * @param  Closure(array, array<string>): array  $transform
     */
    protected function transformData(Closure $transform): void
    {
        $items = DB::table('menu_items')->whereNotNull('data');

        if (! $items->exists()) {
            return;
        }

        $locales = LocaleCollection::map(fn (Locale $locale) => $locale->locale())->unique()->values()->all();

        if ($locales === []) {
            throw new RuntimeException('No locales are registered in the LocaleCollection, so the menu item translations cannot be migrated. Register your locales before running this migration.');
        }

        $items->lazyById()->each(function (object $item) use ($transform, $locales) {
            $data = json_decode($item->data, true);

            if (! is_array($data)) {
                return;
            }

            $transformed = $transform($data, $locales);

            if ($transformed === $data) {
                return;
            }

            DB::table('menu_items')
                ->where('id', $item->id)
                ->update(['data' => json_encode($transformed)]);
        });
    }
};

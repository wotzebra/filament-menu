<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Wotz\FilamentMenu\Models\MenuItem;

return new class extends Migration
{
    /**
     * Menu items saved with wotz/filament-translatable-tabs v3 before the
     * builder converted its output were stored field-first (`label.nl`).
     * Move them back to the locale-first shape the navigation elements read.
     */
    public function up(): void
    {
        DB::table('menu_items')
            ->whereNotNull('data')
            ->lazyById()
            ->each(function (object $item) {
                $data = json_decode($item->data, true);

                if (! is_array($data)) {
                    return;
                }

                $localeFirst = MenuItem::localeFirst($data);

                if ($localeFirst === $data) {
                    return;
                }

                DB::table('menu_items')
                    ->where('id', $item->id)
                    ->update(['data' => json_encode($localeFirst)]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Locale-first is the shape the navigation elements read.
    }
};

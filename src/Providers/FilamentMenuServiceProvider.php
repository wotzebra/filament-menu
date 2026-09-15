<?php

namespace Wotz\FilamentMenu\Providers;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wotz\FilamentMenu\View\Components;

class FilamentMenuServiceProvider extends PackageServiceProvider
{
    protected const PACKAGE_NAME = 'filament-menu';

    public function configurePackage(Package $package): void
    {
        $package
            ->name($this->packageName())
            ->setBasePath(__DIR__ . '/../')
            ->hasMigrations([
                '2023_05_12_083428_create_menus_table',
                '2023_05_12_083501_create_menu_items_table',
                '2024_06_06_083501_add_type_to_menu_items_table',
                '2026_09_15_100000_store_menu_item_translations_locale_first',
            ])
            ->hasConfigFile()
            ->runsMigrations()
            ->hasConfigFile()
            ->hasViews($this->packageName())
            ->hasTranslations();
    }

    public function bootingPackage(): void
    {
        Blade::component('filament-menu::render', Components\MenuRender::class);
        Blade::component('filament-menu::breadcrumbs', Components\MenuBreadcrumbs::class);
    }

    public function packageName(): string
    {
        return self::PACKAGE_NAME;
    }
}

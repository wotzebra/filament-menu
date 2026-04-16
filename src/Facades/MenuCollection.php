<?php

namespace Wotz\FilamentMenu\Facades;

use Illuminate\Support\Facades\Facade;

class MenuCollection extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wotz\FilamentMenu\MenuCollection::class;
    }
}

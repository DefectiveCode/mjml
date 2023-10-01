<?php

declare(strict_types=1);

namespace DefectiveCode\MJML\Facades;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class MJML extends LaravelFacade
{
    protected static function getFacadeAccessor(): string
    {
        return \DefectiveCode\MJML\MJML::class;
    }
}

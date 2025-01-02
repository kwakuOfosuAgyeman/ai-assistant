<?php

namespace Kwakuofosuagyeman\AIAssistant\Facades;

use Illuminate\Support\Facades\Facade;

class AI extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Kwakuofosuagyeman\AIAssistant\AIManager::class;
    }
}

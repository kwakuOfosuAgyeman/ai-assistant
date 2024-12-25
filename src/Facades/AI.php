<?php

namespace YourVendor\AIAssistant\Facades;

use Illuminate\Support\Facades\Facade;

class AI extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ai';
    }
}

<?php

namespace Touhidurabir\MultiKyeRouteBinding\Facades;

use Illuminate\Support\Facades\Facade;

class MultiKyeRouteBinding extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {

        return 'multi-key-route-binding';
    }
}
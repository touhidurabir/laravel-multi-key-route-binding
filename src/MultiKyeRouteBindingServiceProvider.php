<?php

namespace Touhidurabir\MultiKyeRouteBinding;

use Illuminate\Support\ServiceProvider;

class MultiKyeRouteBindingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('multi-key-route-binding', function () {
            return new RouteBindingResolver();
        });

        $this->app->alias('multi-key-route-binding', RouteBindingResolver::class);
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

}

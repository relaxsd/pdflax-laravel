<?php

namespace Pdflax\Laravel;

use Illuminate\Support\ServiceProvider;
use Pdflax\Factory\PdflaxFactory;

class PdflaxServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('relaxsd/pdflax-laravel');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['pdflax'] = $this->app->share(function ($app) {
            return new PdflaxFactory;
        });

        // TODO: Shortcut so developers don't need to add an Alias in app/config/app.php
        //        $this->app->booting(function()
        //        {
        //            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        //            $loader->alias('Pdflax', 'Pdflax\Laravel\PdflaxFacade');
        //        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('pdflax');
    }

}

<?php

namespace Relaxsd\Pdflax\Laravel;

use Illuminate\Support\ServiceProvider;

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

        // Since there is only one PdfCreator implementation (Fpdf), it is required by this project
        // and registered as default here:
        $this->app->make('pdflax-registry')->register('fpdf', 'Relaxsd\Pdflax\Fpdf\FPdfPdfCreator', true);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        // Singleton: One registry containing all PdfCreator implementations
        $this->app->singleton('pdflax-registry', function () {
            return new \Relaxsd\Pdflax\Registry\RegistryWithDefault();
        });

        // Pdflax Facade returns PdfCreatorRegistry object that all use the same
        // PdfCreator registry.
        $this->app->bind('pdflax', function ($app) {
            return new \Relaxsd\Pdflax\Creator\PdfCreatorRegistry(
                $app->make('pdflax-registry')
            );
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['pdflax-registry', 'pdflax'];
    }

}

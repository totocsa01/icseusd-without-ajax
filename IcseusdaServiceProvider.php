<?php

namespace Totocsa\Icseusda;

use Illuminate\Support\ServiceProvider;

class IcseusdaServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Ha van konfigurációs fájl, azt itt töltheted be
        //$this->mergeConfigFrom(__DIR__.'/../config/destroy-confirm-modal.php', 'destroy-confirm-modal');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Totocsa\Icseusda\app\Console\Commands\ModelMetaMakeCommand::class,
                \Totocsa\Icseusda\app\Console\Commands\IndexQueryMakeCommand::class,
                \Totocsa\Icseusda\app\Console\Commands\IcseusdaControllerMakeCommand::class,
                \Totocsa\Icseusda\app\Console\Commands\IcseusdaViewsMakeCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/resources/assets' => public_path('vendor/totocsa/icseusda'),
        ], 'icseusda-assets');
    }
}

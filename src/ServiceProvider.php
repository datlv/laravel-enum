<?php

namespace Datlv\Enum;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Class ServiceProvider
 *
 * @package Datlv\Enum
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'enum');
        $this->loadViewsFrom(__DIR__ . '/../views', 'enum');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $this->publishes([
            __DIR__ . '/../views' => base_path('resources/views/vendor/enum'),
            __DIR__ . '/../lang' => base_path('resources/lang/vendor/enum'),
            __DIR__ . '/../config/enum.php' => config_path('enum.php'),
        ]);

        // pattern filters
        $router->pattern('enum', '[0-9]+');
        // model bindings
        $router->model('enum', EnumModel::class);

        if ($this->app->has('menu-manager')) {
            app('menu-manager')->addItems(config('enum.menus'));
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/enum.php', 'enum');
        $this->app->singleton('enum', function () {
            return new Manager();
        });
        $this->app->booting(function () {
            AliasLoader::getInstance()->alias('Enum', Facade::class);
        });
    }

    public function provides()
    {
        return ['enum'];
    }

}

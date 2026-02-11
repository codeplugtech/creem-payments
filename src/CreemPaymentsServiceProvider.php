<?php
declare(strict_types=1);

namespace Codeplugtech\CreemPayments;

use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreemPaymentsServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/creem.php',
            'creem'
        );
    }

    public function boot(): void
    {
        $this->bootRoute();
        $this->bootPublishing();
        // $this->registerCommands();
        $this->loadFactories();
    }


    protected function bootRoute(): void
    {
        Route::group([
            'prefix' => config('creem.path'),
            'namespace' => 'Codeplugtech\CreemPayments\Http\Controllers',
            'as' => 'creem.',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Boot the package's publishable resources.
     *
     * @return void
     */
    protected function bootPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/creem.php' => $this->app->configPath('creem.php'),
            ], 'creem-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'creem-migrations');
        }
    }

    protected function loadFactories()
    {
        if ($this->app->environment('testing')) {
            Factory::guessFactoryNamesUsing(
                fn(string $modelName) => 'Codeplugtech\\CreemPayments\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
            );
        }
    }

}

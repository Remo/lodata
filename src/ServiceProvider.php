<?php

namespace Flat3\Lodata;

use Flat3\Lodata\Controller\Monitor;
use Flat3\Lodata\Controller\OData;
use Flat3\Lodata\Controller\ODCFF;
use Flat3\Lodata\Controller\PBIDS;
use Illuminate\Support\Facades\Route;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public static function restEndpoint(): string
    {
        return url(self::route()).'/';
    }

    public static function route(): string
    {
        return rtrim(config('lodata.route'), '/');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config.php', 'lodata');
    }

    public static function usingPreview(): bool
    {
        return env('LODATA_PREVIEW', false);
    }

    public function boot()
    {
        $this->app->singleton(Model::class, function () {
            return new Model();
        });

        $this->app->bind('lodata.model', function ($app) {
            return $app->make(Model::class);
        });

        $middleware = config('lodata.middleware', []);

        if (self::usingPreview()) {
            $middleware = [];
        }

        Route::middleware($middleware)->group(function () {
            $route = self::route();

            Route::get("{$route}/_lodata/odata.pbids", [PBIDS::class, 'get']);
            Route::get("{$route}/_lodata/{identifier}.odc", [ODCFF::class, 'get']);
            Route::resource("${route}/_lodata/monitor", Monitor::class);

            Route::any("{$route}{path}", [OData::class, 'handle'])
                ->where('path', '(.*)');
        });
    }
}

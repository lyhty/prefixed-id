<?php

namespace SirMathays\PrefixedId;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SirMathays\PrefixedId\Facades\PrefixedId as Facade;

class PrefixedIdServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/prefixed_id.php', 'prefixed_id');

        $this->publishes([
            __DIR__ . '/../config/prefixed_id.php' => $this->app->configPath('prefixed_id.php'),
        ]);

        $this->app->singleton(
            PrefixedId::class, 
            fn () => new PrefixedId($this->app['config']['prefixed_id'])
        );

        $this->handleModelRouteBindings();
    }

    /**
     * Handle model route bindings.
     *
     * @return void
     */
    protected function handleModelRouteBindings()
    {
        $bindPrefix = Arr::get($this->app['config'], 'prefixed_id.routing.bind_prefix');

        foreach (Arr::flatten(Facade::modelClasses()) as $className) {

            $bindName = (string) Str::of($className)
                ->classBasename()
                ->when(
                    is_null($bindPrefix) || Str::endsWith($bindPrefix, '_'),
                    fn ($str) => $str->camel()
                )
                ->when(
                    !is_null($bindPrefix),
                    fn ($str) => $str->start($bindPrefix)
                );

            Route::bind(
                $bindName,
                fn ($prefixedId) => $className::pidFindOrFail(strtoupper($prefixedId))
            );
        }

        $genericBindName = Arr::get($this->app['config'], 'prefixed_id.routing.generic_bind_name');

        if (!is_null($genericBindName)) {
            Route::bind(
                $genericBindName, 
                fn ($prefixedId) => Facade::findOrFailModel($prefixedId)
            );
        }
    }
}
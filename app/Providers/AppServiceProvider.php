<?php

namespace App\Providers;

use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Passport::tokensExpireIn(now()->addDays(30));

        Passport::refreshTokensExpireIn(
            now()->addDays(60)
        );

        /**
         * migration regsitration
         */

        $this->loadMigrationsFrom([
            __DIR__ . '/../../database/migrations',
        ]);

        /***
         * macros
         */

        Request::macro('prepareBoolean', function (string $key, bool $default = true) {
            if (! $this->has($key)) {
                return $default;
            }

            return filter_var(
                $this->input($key),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ?? $default;
        });

        Request::macro('prepareBooleans', function (array $fields, bool $default = true) {
            return collect($fields)->mapWithKeys(fn($field) => [
                $field => $this->prepareBoolean($field, $default),
            ])->toArray();
        });
    }
}
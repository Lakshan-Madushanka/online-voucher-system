<?php

namespace App\Providers;

use App\Repository\Auth\SanctumAuthRepository;
use App\Repository\SanctumAuthRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SanctumAuthRepositoryInterface::class, function () {
            return new SanctumAuthRepository();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Password::defaults(function () {
            $rule = Password::min(6)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();

            return $this->app->isProduction() ? $rule : Password::min(4);
        });
    }
}

<?php

namespace App\Providers;

use App\Repository\Auth\SanctumAuthRepository;
use App\Repository\SanctumAuthRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
       /* $this->app->bind(SanctumAuthRepositoryInterface::class, function () {
            return new SanctumAuthRepository();
        });*/
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

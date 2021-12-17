<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Voucher;
use App\Policies\VoucherPolicy;
use App\Services\UserService;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies
        = [
            Voucher::class => VoucherPolicy::class
        ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('isAdministrative', function (User $user) {
            return UserService::checkRole('isAdministrative') ? Response::allow()
                : Response::deny('Admin type access required ');
        });
    }
}

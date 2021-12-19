<?php

namespace App\Providers;

use App\Models\CashVoucher;
use App\Models\Voucher;
use App\Repository\Auth\SanctumAuthRepository;
use App\Repository\CashVoucherRepositoryInterface;
use App\Repository\Eloquent\CashVoucherRepository;
use App\Repository\Eloquent\VoucherRepository;
use App\Repository\SanctumAuthRepositoryInterface;
use App\Repository\VoucherRepositoryInterface;
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

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(SanctumAuthRepositoryInterface::class, function () {
            return new SanctumAuthRepository();
        });

        $this->app->bind(VoucherRepositoryInterface::class, function () {
            return new VoucherRepository(new Voucher());
        });

        $this->app->bind(CashVoucherRepositoryInterface::class, function () {
            return new CashVoucherRepository(new CashVoucher());
        });

    }
}

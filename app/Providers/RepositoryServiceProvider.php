<?php

namespace App\Providers;

use App\Models\CashVoucher;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\User;
use App\Models\Voucher;
use App\Repository\Auth\SanctumAuthRepository;
use App\Repository\CashVoucherRepositoryInterface;
use App\Repository\Eloquent\CashVoucherRepository;
use App\Repository\Eloquent\PurchaseDetailRepository;
use App\Repository\Eloquent\PurchaseRepository;
use App\Repository\Eloquent\UserRepository;
use App\Repository\Eloquent\VoucherRepository;
use App\Repository\PurchaseDetailRepositoryInterface;
use App\Repository\PurchaseRepositoryInterface;
use App\Repository\SanctumAuthRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Repository\VoucherRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
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

        $this->app->bind(UserRepositoryInterface::class, function () {
            return new UserRepository(new User());
        });

        $this->app->bind(PurchaseRepositoryInterface::class, function () {
            return new PurchaseRepository(new Purchase());
        });

        $this->app->bind(PurchaseDetailRepositoryInterface::class, function () {
            return new PurchaseDetailRepository(new PurchaseDetail());
        });

    }
}

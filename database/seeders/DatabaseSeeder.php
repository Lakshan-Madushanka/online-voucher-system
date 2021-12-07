<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Role;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(3)->create()->each(function ($user, $key) {
            $user->roles()->attach(array_values(Role::types)[$key]);
        });

        User::factory()->count(10)
            ->hasRoles(1)
            ->has(Voucher::factory()->count(3))
            ->hascashVouchers(2)
            ->create();

        $purchases = Purchase::all()->each(function($purchase) {
            $purchaseDetail = PurchaseDetail::factory()->make();
            $purchase->details()->save($purchaseDetail);
        });
    }
}

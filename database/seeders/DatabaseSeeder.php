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
        $roleValues = array_values(Role::types);

        User::factory()->count(3)->create()->each(function ($user, $key) use ($roleValues){
            $user->roles()->attach($roleValues[$key]);
        });

        User::factory()->count(10)
            //->hasRoles(1)
            ->has(Voucher::factory()->count(3))
            ->hascashVouchers(2)
            ->create()
            ->each(function ($user, $key) use($roleValues)  {
                //echo $key , PHP_EOL;
                $index = floor($key/3);
                    if($index == 3) {
                        $index = 2;
                    }
                $user->roles()->attach($roleValues[$index]);
            });;

        $purchases = Purchase::all()->each(function($purchase) {
            $purchaseDetail = PurchaseDetail::factory()->make();
            $purchase->details()->save($purchaseDetail);
        });
    }
}

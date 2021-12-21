<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VoucherPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test__validate__at__least__one__of__regularVoucher__or__cashVoucher__field__reqired(
    )
    {
        Sanctum::actingAs($this->getUser());

        $response = $this->json('post', route('api.vouchers-purchases.store'));

        $response->assertStatus(422);
        $response->assertJsonPath('errors.regularVouchers',
            [__('validation.voucherRequiredWithout')]);
    }

    public function test__voucher__id__field__is__valdated()
    {
        $user = $this->getUser();

        Sanctum::actingAs($user);

        $payload  = $this->getValidDataFormat($user->id);
        $payload2 = $payload;

        unset($payload['regularVouchers'][0]['voucher_Id']);
        unset($payload2['cashVouchers'][0]['voucher_Id']);

        $response1 = $this->json('post', route('api.vouchers-purchases.store'),
            $payload);
        $response2 = $this->json('post', route('api.vouchers-purchases.store'),
            $payload2);

        $response1->assertStatus(422);
        $response2->assertStatus(422);

    }

    public function test__voucher__receiver__id__field__is__valdated()
    {
        $user = $this->getUser();

        Sanctum::actingAs($user);

        $payload = $this->getValidDataFormat($user->id);

        $payload['regularVouchers'][1]['type'] = 'presented';

        //without receiver id
        $response1 = $this->json('post', route('api.vouchers-purchases.store'),
            $payload);

        //with receiver id equal to auth user id
        $payload['regularVouchers'][1]['receiver_id'] = $user->id;
        $response2                                    = $this->json('post',
            route('api.vouchers-purchases.store'), $payload);

        $response1->assertStatus(422);
        $response2->assertStatus(422);

    }

    public function test__users__can__buy__more__than__one__vouchers__in__any__type__once(
    )
    {
        $user = $this->getUser();

        Sanctum::actingAs($user);

        $payload        = $this->getValidDataFormat($user->id);
        $cashVoucherPurchases = count($payload['regularVouchers']);
        $regularVoucherPurchases = count($payload['cashVouchers']);
        $totalPurchases = $cashVoucherPurchases + $regularVoucherPurchases;

        $response = $this->json('post', route('api.vouchers-purchases.store'),
            $payload);

        $response->status(200);
        $response->assertJson(function (AssertableJson $json) {
            $json->hasAll('status', 'status_message', 'data')
                ->has('data', 3)
                ->etc();
        });
        $response->assertJsonPath('data.total_purchases', $totalPurchases);
        $response->assertJsonPath('data.cash_voucher_purchases', $cashVoucherPurchases);
        $response->assertJsonPath('data.regular_voucher_purchases', $regularVoucherPurchases);

    }

    private function getValidDataFormat(int $authId)
    {
        return [
            "regularVouchers" => [
                [
                    "voucher_Id" => 3,
                    "user_Id"    => $authId,
                    "quantity"   => 1,
                    "type"       => "direct",

                ],
                [
                    "voucher_Id" => 3,
                    "user_Id"    => $authId,
                    "quantity"   => 1,
                    "type"       => "direct",

                ],
            ],

            "cashVouchers" => [
                [
                    "voucher_Id" => 3,
                    "user_Id"    => $authId,
                    "quantity"   => 1,
                    "type"       => "direct",

                ],
                [
                    "voucher_Id" => 3,
                    "user_Id"    => $authId,
                    "quantity"   => 1,
                    "type"       => "direct",

                ],
            ],

        ];
    }

    public function getUser()
    {
        return User::first();
    }
}

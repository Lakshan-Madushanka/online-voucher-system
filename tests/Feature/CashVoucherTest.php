<?php

namespace Tests\Feature;

use App\Models\CashVoucher;
use App\Models\Role;
use App\Models\User;
use App\Models\Voucher;
use App\Repository\Eloquent\CashVoucherRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CashVoucherTest extends TestCase
{
    use RefreshDatabase;

    private $cashVoucherRepo;

    protected function setUp(): void
    {
        $this->cashVoucherRepo = new CashVoucherRepository(new CashVoucher());
        parent::setUp();
    }

    public function test__cash_voucher_value_can_be_validated()
    {
        Sanctum::actingAs($this->getUser(Role::SUPER_ADMIN));

        $cashVoucher = $this->generateCashVoucher();

        //with empty value
        $cashVoucher['price'] = null;
        $response1            = $this->json('post',
            route('api.cash-vouchers.store'), $cashVoucher);

        //without multification of 500
        $cashVoucher['price'] = 1200;
        $response2            = $this->json('post',
            route('api.cash-vouchers.store'), $cashVoucher);

        // with existing price value
        $cashVoucher['price'] = $this->getMaxPrice();
        $response3            = $this->json('post',
            route('api.cash-vouchers.store'), $cashVoucher);

        $response1->assertStatus(422);
        $response2->assertStatus(422);
        $response2->assertJsonPath('errors.price',
            [__('validation.cashVoucherPrice')]);
        $response3->assertStatus(422);

    }

    public function test__customers__cannot__add_cash__vouchers()
    {
        Sanctum::actingAs($this->getUser(Role::CUSTOMER));

        $voucher = $this->generateCashVoucher();

        $response = $this->json('post', route('api.cash-vouchers.store'),
            $voucher);

        $response->assertStatus(403);
    }

    public function test__admin_type__users__can__add_cash__vouchers()
    {
        Sanctum::actingAs($this->getUser(Role::SUPER_ADMIN));
        $voucher   = $this->generateCashVoucher();
        $response1 = $this->json('post', route('api.cash-vouchers.store'),
            $voucher);

        Sanctum::actingAs($this->getUser(Role::ADMIN));
        $voucher   = $this->generateCashVoucher();
        $response2 = $this->json('post', route('api.cash-vouchers.store'),
            $voucher);

        $response1->assertStatus(200);
        $response2->assertStatus(200);
    }

    public function test__users__can__obtain__cash_voucher_by_id()
    {
        $cashVoucher = $this->cashVoucherRepo->find(1);

        $resonse = $this->json('get',
            route('api.cash-vouchers.show', ['cash_voucher' => $cashVoucher->id]));

        $this->assertNotNull($resonse);
        $resonse->assertStatus(200);
    }

    public function test__users__can__obtain__all_cash_vouchers()
    {
        $vouchers = $this->cashVoucherRepo->all();

        $noOfVouchers = $vouchers->count();

        $response = $this->json('get', route('api.cash-vouchers.index'));

        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) use ($noOfVouchers
        ) {
            $json->hasAll('status', 'status_message', 'data')
                ->has('data', $noOfVouchers)
                ->etc();
        });
    }

    public function test__cash__voucher__prices__can_be_searched_by_price_range(
    )
    {
        Sanctum::actingAs($this->getUser(Role::SUPER_ADMIN));

        $results = $this->cashVoucherRepo->getByPriceRange(100, 10000);

        $noOfResults = $results->count();

        $response = $this->json('get',
            route('api.cash-vouchers.search.price_range',
                ['min' => 100, 'max' => 10000]));

        $response->assertStatus(200);
        $response
            ->assertJson(function (AssertableJson $json) use ($noOfResults) {
                $json->hasAll('status', 'status_message', 'data')
                    ->has('data', $noOfResults)
                    ->etc();
            });
    }

    public function test__admin_type__users__can__delete_more_than_one_cash__vouchers(
    )
    {
        Sanctum::actingAs($this->getUser(Role::SUPER_ADMIN));

        $ids = $this->getVouchers(3)->pluck('id')->toArray();

        $response1 = $this->json('delete',
            route('api.cash-vouchers.delete_many',
                ['ids' => $ids]));

        $response1->assertStatus(200);
    }

    public function test__admin_type__users__can__delete_cash__vouchers()
    {
        Sanctum::actingAs($this->getUser(Role::SUPER_ADMIN));

        $vouchers = $this->getVouchers(2)->pluck('id')->toArray();
        $id1      = $vouchers[0];
        $id2      = $vouchers[1];

        $response1 = $this->json('delete', route('api.cash-vouchers.destroy',
            ['cash_voucher' => $id1]));

        Sanctum::actingAs($this->getUser(Role::ADMIN));
        $response2 = $this->json('delete', route('api.cash-vouchers.destroy',
            ['cash_voucher' => $id2]));

        $response1->assertStatus(200);
        $response2->assertStatus(200);
    }

    function getUser(string $type): Model
    {
        $user = User::whereRelation('roles', 'type', $type)->first();

       // $response = $this->json('get', route())

        return $user;
    }

    public function generateCashVoucher()
    {
        $voucher          = CashVoucher::factory()->make()->toArray();
        $voucher['price'] = $this->getMaxPrice() + 500;

        return $voucher;
    }

    public function getMaxPrice()
    {
        $value = CashVoucher::max('price');

        return $value;
    }

    public function getVoucher()
    {
        return CashVoucher::inRandomOrder()->first();
    }

    public function getVouchers(int $noOfVouchers)
    {
        $vouchers = CashVoucher::inRandomOrder()->limit($noOfVouchers)->get();

        return $vouchers;
    }
}

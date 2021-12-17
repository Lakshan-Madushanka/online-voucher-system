<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Voucher;
use App\Repository\Eloquent\VoucherRepository;
use App\Repository\VoucherRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use RefreshDatabase;

    // protected $seeder = VoucherSeeder::class;
    protected $voucherRepo;


    /**
     * A basic feature test example.
     *
     * @return void
     */


    public function setUp(): void
    {
        parent::setUp();
        $this->voucherRepo = new VoucherRepository(new Voucher());
    }

    /*public function __construct(VoucherRepositoryInterface $voucherRepo)
    {
        $this->voucherRepo = $voucherRepo;
    }*/

    public function test__customers_can_not_obtain_only_approved_vouchers_using_id(
    )
    {
        $voucher
            = $this->voucherRepo->getByStatus(['status1' => Voucher::STATUS['PENDING']])
            ->first();

        $response = $this->json('get',
            route('api.vouchers.show', ['voucher' => $voucher['id']]));

        $response->assertStatus(403);

    }

    public function test__customers_can_obtain_approved_voucher_using_id()
    {
        $voucher
            = $this->voucherRepo->getByStatus(['status1' => Voucher::STATUS['APPROVED']])
            ->first();

        $response = $this->json('get',
            route('api.vouchers.show', ['voucher' => $voucher['id']]));

        $response->assertStatus(200);

        $response->assertJson(function (AssertableJson $json) use ($voucher) {
            $json->hasAll('status', 'status_message', 'data')
                ->has('data', 8)
                ->has('data', function ($json) use ($voucher) {
                    $json->where('id', $voucher['id'])
                        ->where('price', $voucher['price'])
                        ->where('terms', $voucher['terms'])
                        ->where('image', $voucher['image'])
                        ->etc();
                });
        });
    }

    public function test__customer__can__obtain__all__approved_voucher__details(
    )
    {
        $results
            = $this->voucherRepo->getByStatus(['status1' => Voucher::STATUS['APPROVED']]);

        $noOfResults  = $results->count();
        $lastResultNo = $noOfResults - 1;
        $lastResult   = $results[$noOfResults - 1];

        $response = $this->json('get', route('api.vouchers.index'));

        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) use (
            $noOfResults,
            $lastResult,
            $lastResultNo
        ) {
            $json->hasAll('status', 'status_message', 'data')
                ->has('data', $noOfResults)
                ->has('data.'.$lastResultNo,
                    function ($json) use ($lastResult) {
                        $json->where('id', $lastResult->id)
                            ->where('status', $lastResult->status)
                            ->where('price', $lastResult->price)
                            ->etc();
                    });
        });
    }

    public function test__status__can__be_searched_by_admin_type_users()
    {
        $user = $this->getUser('admin');
        Sanctum::actingAs($user);

        $aprovedVouchers = Voucher::where('status', Voucher::STATUS['APPROVED'])
            ->get();
        $noOfResults1    = $aprovedVouchers->count();

        $rejectedVouchers = Voucher::where('status',
            Voucher::STATUS['REJECTED'])->get();
        $noOfResults2     = $rejectedVouchers->count();

        $pendingVouchers = Voucher::where('status', Voucher::STATUS['PENDING'])
            ->get();
        $noOfResults3    = $pendingVouchers->count();

        $pendingAndApprovedVouchers = Voucher::where('status',
            Voucher::STATUS['PENDING'])
            ->orWhere('status', Voucher::STATUS['APPROVED'])->get();
        $noOfResults4               = $pendingAndApprovedVouchers->count();


        $response1 = $this->json('get', route('api.vouchers.search.status', [
            'status1' => Voucher::STATUS['APPROVED'],
        ]));

        $response2 = $this->json('get', route('api.vouchers.search.status', [
            'status3' => Voucher::STATUS['REJECTED'],
        ]));

        $response3 = $this->json('get', route('api.vouchers.search.status', [
            'status2' => Voucher::STATUS['PENDING'],
        ]));

        $response4 = $this->json('get', route('api.vouchers.search.status', [
            'status2' => Voucher::STATUS['PENDING'],
            'status1' => Voucher::STATUS['APPROVED'],

        ]));

        $response1->assertStatus(200);
        $response1
            ->assertJson(function (AssertableJson $json) use ($noOfResults1) {
                $json->hasAll('status', 'status_message', 'data')
                    ->has('data', $noOfResults1);

            });

        $response2->assertStatus(200);
        $response2
            ->assertJson(function (AssertableJson $json) use ($noOfResults2) {
                $json->hasAll('status', 'status_message', 'data')
                    ->has('data', $noOfResults2);

            });

        $response3->assertStatus(200);
        $response3
            ->assertJson(function (AssertableJson $json) use ($noOfResults3) {
                $json->hasAll('status', 'status_message', 'data')
                    ->has('data', $noOfResults3);

            });

        $response4->assertStatus(200);
        $response4
            ->assertJson(function (AssertableJson $json) use ($noOfResults4) {
                $json->hasAll('status', 'status_message', 'data')
                    ->has('data', $noOfResults4);

            });

    }

    public function test__voucher__prices__can_be_searched_by_price_range()
    {
        $user = $this->getUser('admin');

        Sanctum::actingAs($user);

        $results = $this->voucherRepo->getByPriceRange(100, 10000);

        $noOfResults = $results->count();

        $response = $this->json('get', route('api.vouchers.search.price_range',
            ['min' => 100, 'max' => 10000]));

        $response->assertStatus(200);
        $response
            ->assertJson(function (AssertableJson $json) use ($noOfResults) {
                $json->hasAll('status', 'status_message', 'data')
                    ->has('data', $noOfResults)
                    ->etc();
            });
    }

    public function test_voucher_validity_date_is_validated()
    {
        $voucher = $this->generateVoucher();


        Sanctum::actingAs(User::factory()->create());
        //valid for 7 month periods
        $voucher['validity'] = Carbon::now()->addMonths(7)->toDateTimeString();
        $response1           = $this->json('post', route('api.vouchers.store'),
            $voucher);

        //valid for 3years periods
        $voucher['validity'] = Carbon::now()->addYears(3)->toDateTimeString();
        $response2           = $this->json('post', route('api.vouchers.store'),
            $voucher);

        //valid for 4years periods
        $voucher['validity'] = Carbon::now()->addYears(4)->toDateTimeString();
        $response3           = $this->json('post', route('api.vouchers.store'),
            $voucher);

        $response1->assertStatus(422);
        $response2->assertStatus(422);
        $response3->assertStatus(422);

    }

    public function test__uploaded_file_name_length_is_validated()
    {
        $voucher = $this->generateVoucher();

        //length over 25
        $voucher['image'] = UploadedFile::fake()->image(Str::random()
            .Str::random().'jpg');

        Sanctum::actingAs(User::factory()->create());
        $response = $this->json('post', route('api.vouchers.store'), $voucher);

        $response->assertStatus(422);
    }

    public function test_uploaded_file_name_should_not_alredy_exists()
    {
        Storage::fake('public');

        $voucher = $this->generateVoucher();

        $authUser = User::factory()->create();
        $authUser->roles()->attach(Role::types['super_admin']);

        Sanctum::actingAs($authUser);

        $response1 = $this->json('post', route('api.vouchers.store'), $voucher);
        $response2 = $this->json('post', route('api.vouchers.store'), $voucher);

        $response2->assertStatus(422);
        $response2->assertJsonPath('errors.image',
            ["image with same name already exists, please choose a different name"]
        );

    }

    public function test_customers_are_not_allowed_to_create_vouchers()
    {
        //create  customer type user
        $authUser = User::factory()->create();
        $authUser->roles()->attach(Role::types['customer']);

        $voucher = $this->generateVoucher();

        Sanctum::actingAs($authUser);
        $response = $this->json('post', route('api.vouchers.store'), $voucher);

        $response->assertStatus(403);

    }

    public function test_admins_can_create_vouchers_with_status_pending_only()
    {
        Storage::fake('public');

        //create  customer type user
        $authUser = User::factory()->create();
        $authUser->roles()->attach(Role::types['admin']);

        $voucher1           = $this->generateVoucher();
        $voucher2           = $this->generateVoucher();
        $voucher1['status'] = 'pending';
        $voucher2['status'] = 'approved';

        Sanctum::actingAs($authUser);
        $response1 = $this->json('post', route('api.vouchers.store'),
            $voucher1);
        $response2 = $this->json('post', route('api.vouchers.store'),
            $voucher2);

        $response1->assertStatus(201);
        $response1->assertJsonPath('data.status', 'pending');
        $response2->assertStatus(403);

    }

    public function test_super_admins_can_create_vouchers_with_status_approved()
    {
        Storage::fake('public');

        //create  customer type user
        $authUser = User::factory()->create();
        $authUser->roles()->attach(Role::types['super_admin']);

        $voucher           = $this->generateVoucher();
        $voucher['status'] = 'approved';

        Sanctum::actingAs($authUser);
        $response = $this->json('post', route('api.vouchers.store'), $voucher);

        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'approved');

    }

    public function test_voucher_cannot_be_updated_without_valid_validity_period(
    )
    {

        $user      = $this->getUser('admin');
        $voucher   = $this->getVoucher();
        $voucherId = $voucher->id;
        // 7 months
        $validity = $voucher->created_at->addMonths(7);

        $payload             = $voucher->toArray();
        $payload['validity'] = $validity;

        Sanctum::actingAs($user);
        $response = $this->json('patch',
            route('api.vouchers.update', ['voucher' => $voucherId]), $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.validity',
            [__('validation.voucherValidity')]);
    }

    public function test_voucher_cannot_be_updated_without_changing_one_attribute_at_least(
    )
    {
        Storage::fake('public');

        $user      = $this->getUser('super_admin');
        $voucher   = $this->getVoucher();
        $voucherId = $voucher->id;

        $payload = $voucher->toArray();

        Sanctum::actingAs($user);
        $response1 = $this->json('patch',
            route('api.vouchers.update', ['voucher' => $voucherId]), $payload);

        unset($payload['image']);
        $response2 = $this->json('patch',
            route('api.vouchers.update', ['voucher' => $voucherId]), $payload);

        $response1->assertStatus(201);
        $response2->assertStatus(400);
        $response2->assertJsonPath('errors.error',
            __('validation.isDirty'));
    }

    public function test_voucher_can_be_updated_successfully()
    {
        Storage::fake('public');

        $voucher        = $this->getVoucher();
        $voucher->terms = Str::random(16);

        $user = $this->getUser('super_admin');

        Sanctum::actingAs($user);

        $resonse = $this->json('patch',
            route('api.vouchers.update', ['voucher' => $voucher->id]),
            $voucher->toArray());

        $resonse->assertStatus(201);
        $resonse->assertJsonPath('data.terms', $voucher->terms);
    }

    public function test__only__super_admin__can_update__voucher_update_to_approved(
    )
    {
        Storage::fake('public');

        $voucher         = Voucher::where('status', 'rejected')
            ->orWhere('status', 'pending')->first();
        $originalStatus  = $voucher->status;
        $voucher->status = 'approved';

        $playLoad = $voucher->toArray();
        unset($playLoad['image']);

        // acting as admin
        $user = $this->getUser('admin');
        Sanctum::actingAs($user);
        $resonse1 = $this->json('patch',
            route('api.vouchers.update', ['voucher' => $voucher->id]),
            $playLoad);

        // acting as super admin
        $payload['status'] = 'approved';
        $user              = $this->getUser('super_admin');
        Sanctum::actingAs($user);
        $resonse2 = $this->json('patch',
            route('api.vouchers.update', ['voucher' => $voucher->id]),
            $playLoad);


        $resonse1->assertStatus(403);

        $resonse2->assertStatus(201);
        $resonse2->assertJsonPath('data.status', 'approved');
    }

    public function test_voucher_image_can_be_updated_with_previous_name()
    {
        Storage::fake('public');

        $fileName1 = uniqid().'.jpg';
        $image1    = UploadedFile::fake()->image($fileName1);
        /* $fileName2 = uniqid().'.jpg';
         $image2    = UploadedFile::fake()->image($fileName2);*/

        $voucher = $this->generateVoucher();

        $payload1          = $voucher;
        $payload1['image'] = $image1;

        $payload2          = $voucher;
        $payload2['image'] = $image1;
        $payload2['terms'] = Str::random(12);

        $user = $this->getUser('super_admin');
        Sanctum::actingAs($user);

        //store first record
        $response1 = $this->json('post', route('api.vouchers.store'),
            $payload1);
        $response1->status(201);

        $createdVoucher = Voucher::latest()->first();
        Storage::disk('public')->assertExists($createdVoucher->image);

        //update the same record with new image
        $response2 = $this->json('patch',
            route('api.vouchers.update', ['voucher' => $createdVoucher->id]),
            $payload2);

        $response2->status(201);
        Storage::disk('public')->assertExists(Voucher::storagePath.'/'
            .$fileName1);

    }

    public function test_voucher_image_cannot_be_updated_with_existing_image_name_of_different_voucher(
    )
    {
        Storage::fake('public');

        $fileName1 = uniqid().'.jpg';
        $image1    = UploadedFile::fake()->image($fileName1);
        $fileName2 = uniqid().'.jpg';
        $image2    = UploadedFile::fake()->image($fileName2);

        $voucher = $this->generateVoucher();

        $payload1          = $voucher;
        $payload1['image'] = $image1;
        $payload2          = $voucher;
        $payload2['image'] = $image2;


        $user = $this->getUser('super_admin');
        Sanctum::actingAs($user);

        //store first record
        $response1 = $this->json('post', route('api.vouchers.store'),
            $payload1);
        $response2 = $this->json('post', route('api.vouchers.store'),
            $payload2);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $createdVoucher2 = Voucher::orderBy('id', 'desc')->first();

        $payload3          = $createdVoucher2->toArray();
        $payload3['image'] = $image1;

        $response3 = $this->json('patch',
            route('api.vouchers.update', ['voucher' => $payload3['id']]),
            $payload3);

        $response3->assertStatus(422);
        $response3->assertJsonPath('errors.file',
            [__('validation.fileExists')]);

    }

    public function test_old_image_is_deleted_and_new_image_is_stored_when_uploading_new_image_when_voucher_updating(
    )
    {
        Storage::fake('public');

        $fileName1 = uniqid().'.jpg';
        $image1    = UploadedFile::fake()->image($fileName1);
        $fileName2 = uniqid().'.jpg';
        $image2    = UploadedFile::fake()->image($fileName2);

        $voucher = $this->generateVoucher();

        $payload1          = $voucher;
        $payload1['image'] = $image1;
        $payload2          = $voucher;
        $payload2['image'] = $image2;

        $user = $this->getUser('super_admin');
        Sanctum::actingAs($user);

        //store first record
        $response1 = $this->json('post', route('api.vouchers.store'),
            $payload1);
        $response1->status(201);

        $createdVoucher = Voucher::latest()->first();
        Storage::disk('public')->assertExists($createdVoucher->image);

        //update the same record with new image
        $response2 = $this->json('patch',
            route('api.vouchers.update', ['voucher' => $createdVoucher->id]),
            $payload2);
        Storage::disk('public')->assertMissing($createdVoucher->image);
        Storage::disk('public')->assertExists(Voucher::storagePath.'/'
            .$fileName2);

    }

    public function test__vouchers__can__only_be_delete_by_admin_type_users()
    {
        Sanctum::actingAs($this->getUser('customer'));

        $voucher = $this->getVoucher();

        $response = $this->json('delete',
            route('api.vouchers.destroy', ['voucher' => $voucher->id]));

        $response->assertStatus(403);
    }

    public function test__vouchers__can__only_delete_by_admin_type_users()
    {
        Sanctum::actingAs($this->getUser('admin'));

        $voucher = $this->getVoucher();

        $response = $this->json('delete',
            route('api.vouchers.destroy', ['voucher' => $voucher->id]));

        $response->assertStatus(200);
    }

    public function test__more_than_one_vouchers__can__only_delete_by_admin_type_users(
    )
    {
        Sanctum::actingAs($this->getUser('admin'));

        $voucherIds = $this->voucherRepo->all()->take(2)->pluck('id')->toArray();

        $response = $this->json('delete', route('api.vouchers.delete_many'),
            [
                'ids' => $voucherIds
            ]);

        $response->assertStatus(200);
    }

    function generateVoucher()
    {
        $voucher             = Voucher::factory()->make()->toArray();
        $voucher['image']    = UploadedFile::fake()->image(uniqid().'.jpg');
        $voucher['validity'] = now()->addMonth(6)->toDateTimeString();

        return $voucher;
    }

    function getUser(string $type): Model
    {
        $user = User::whereRelation('roles', 'type', $type)->first();

        return $user;
    }

    private function getVoucher()
    {
        $voucher             = Voucher::inRandomOrder()->first();
        $voucher['image']    = UploadedFile::fake()->image(uniqid().'.jpg');
        $voucher['validity'] = $voucher->created_at->addMonths(6)
            ->toDateTimeString();

        return $voucher;
    }
}

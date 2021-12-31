<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repository\Auth\ApiAuthRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    private $tokenService;

    public function setup() : void
    {
      $this->tokenService = new ApiAuthRepository(new User());
      parent::setUp();

    }

    public function test_user_can_obtain_api_token()
    {
        $createdUser = $this->createUser();
        $email = $createdUser->email;
        $password = 'password*&123';
        $device_name = 'mobile';

        $response = $this->json('post', route('api.login'),
            compact('email', 'password', 'device_name'));

        $tokenDetails = $this->tokenService->getTokenDetails($createdUser->id);
        $response->assertStatus(200);

        $response->assertJson(function (AssertableJson $json) use($email) {
            $json->hasAll('status', 'status_message', 'data')
                ->has('data', 8);
        });

        $this->assertNotNull($tokenDetails);

    }

    public function test_user_can_revoke_tokens()
    {
        $createdUser = $this->createUser();
        $email = $createdUser->email;
        $password = 'password*&123';
        $device_name = 'mobile';

        $response1 = $this->json('post', route('api.login'),
            compact('email', 'password', 'device_name'));

        Sanctum::actingAs($createdUser);

        $response2 = $this->json('get', route('api.logout'));

        $tokenDetails = $this->tokenService->getTokenDetails($createdUser->id);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $this->assertNull($tokenDetails);


    }

    public function getUser()
    {
        return User::inRandomOrder()->first();
    }

    public function createUser()
    {
        return User::factory()
            ->create(['password' => Hash::make('password*&123')]);
    }
}

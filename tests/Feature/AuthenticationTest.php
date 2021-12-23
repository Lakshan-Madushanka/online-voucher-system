<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    //////////////////User input validations/////////////////////

    public function test_user_request_has_valid_name()
    {
        $user1         = $this->generateUser();
        $user1['name'] = '';
        $response1     = $this->json('post', route('api.spa_register'), $user1);

        $response1->assertStatus(422);
    }

    public function test_user_request_has_valid_password()
    {
        //empty password
        $user2             = $this->generateUser();
        $user2['password'] = '';
        $response2         = $this->json('post', route('api.spa_register'),
            $user2);

        // password name length less than 6
        $user2['password'] = 'lak';
        $user2['name']     = 'lakshan';
        $response3         = $this->json('post', route('api.spa_register'),
            $user2);


        $response2->assertStatus(422);
        $response3->assertStatus(422);
    }

    public function test_user_request_has_valid_email()
    {
        //invalid email
        $user          = $this->generateUser();
        $user['email'] = 'lakshan.com';
        $response      = $this->json('post', route('api.spa_register'), $user);

        $response->assertStatus(422);
    }

    //////////////////end of user validations/////////////////////

    public function test_user_can_login_successfully()
    {
        $this->testUserLoginFunctionality();

    }

    public function test_logged_user_can_be_remembered()
    {
        $user               = $this->generateUser();
        $user['rememberMe'] = true;

        $response = $this->json('post', route('api.spa_register'), $user);
        $response = $this->json('post', route('api.spa_login'), $user, [
            'origin' => Config::get('app.url'),
        ]);

        $authUser = User::where('email', $user['email'])->first();
        $authUser->makeVisible('remember_me');

        $this->assertNotNull($authUser->remember_token);
        $this->assertNotNull(Auth::user());
        $response->assertJsonPath('data.rememberMe', true);
    }


    public function test_user_can_register_successfully()
    {
        $user = $this->generateUser();

        $response = $this->json('post', route('api.spa_register'), $user, [
            'origin' => Config::get('app.url'),
        ]);

        $registerdUser = User::where('email', $user['email'])->get();

        $this->assertNotNull($registerdUser);
        $response->assertJsonPath('data.name', $user['name']);
    }

    public function test_user_can_logout_successfully()
    {
        Sanctum::actingAs(User::factory()->create());
        $response = $this->actingAs(User::factory()->create())
            ->json('post', route('api.spa_logout'));

        $this->assertNull(Auth::guard('web')->user());
    }

    public function test_user_can_receive_email_verificaton_link()
    {
        $user = $this->generateUser();

        $response = $this->json('post', route('api.spa_register'), $user, [
            'origin' => Config::get('app.url'),
        ]);

        $registeredUser = User::where('email', $user['email'])->first();

        $response = $this->json('get',
            route('verification.send', ['id' => $registeredUser->id]));

        $response->assertStatus(200);

    }

    public function test_user_can_reset_password()
    {
        $user = User::inRandomOrder()->first();

        $response1 = $this->json('post', route('password.forgot'),
            ['email' => $user->email]);

        $resetToken = DB::table('password_resets')
            ->whereEmail($user->email)
            ->value('token');

        $response2 = $this->json('post', route('reset.password'),
            [
                'email' => $user->email,
                'password' => 'newPassword@#1',
                'token' => (string)$resetToken
            ]);

        $this->assertNotNull($resetToken);
       // $response2->assertStatus(200);
    }


    public function generateUser()
    {
        $user             = User::factory()->make()->toArray();
        $user['name']     = 'lakshan';
        $user['password'] = 'lakshn@#123Lk';

        return $user;
    }

    public function testUserLoginFunctionality()
    {
        $user     = $this->generateUser();
        $response = $this->json('post', route('api.spa_register'), $user);
        $response = $this->json('get', '/sanctum/csrf-cookie', $user);
        $response = $this->json('post', route('api.spa_login'), $user, [
            'origin' => Config::get('app.url'),
        ]);

        $this->assertNotNull(Auth::user());
        $this->assertAuthenticated('web');
        $response->assertJsonPath('data.name', $user['name']);
    }
}

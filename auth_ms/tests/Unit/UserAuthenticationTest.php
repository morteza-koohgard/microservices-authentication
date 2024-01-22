<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_validation_of_store_user_api()
    {
        // test phone number length and password length
        $response = $this->postJson('/api/user/create', [
            'name' => 'morteza',
            'phone_number' => '091122',
            'password' => '1234578',
        ]);
        $response->assertStatus(422);
        // check error message has phone number error
        $this->assertTrue(array_key_exists('phone_number', $response->json()['data']['errors']));
        $this->assertTrue(array_key_exists('password', $response->json()['data']['errors']));

        // now check user creation with api
        $response = $this->postJson('/api/user/create', [
            'name' => 'morteza',
            'phone_number' => '09112223344',
            'password' => '12345678',
        ]);
        $response->assertStatus(200);
        $this->assertTrue($response->json()['data']['message'] == 'کاربر با موفقیت ثبت نام شد');

        $this->assertDatabaseHas('users', [
            'phone_number' => '09112223344'
        ]);
    }

    public function test_login_api()
    {
        // first create a user
        User::create([
            'name' => 'morteza',
            'phone_number' => '09223334444',
            'password' => Hash::make('12345678')
        ]);

        // test phone number length and password length
        $response = $this->postJson('/api/user/login', [
            'phone_number' => '091122',
            'password' => '123578',
        ]);

        $response->assertStatus(422);
        // check error message has phone number error
        $this->assertTrue(array_key_exists('phone_number', $response->json()['data']['errors']));
        $this->assertTrue(array_key_exists('password', $response->json()['data']['errors']));

        // now check user login with api
        $response = $this->postJson('/api/user/login', [
            'phone_number' => '09223334444',
            'password' => '12345678'
        ]);
        $response->assertStatus(200);
        $this->assertTrue($response->json()['data']['message'] == 'کاربر با موفقیت وارد شد');
    }

    public function test_logout_api()
    {
        // first create a user and get token
        $response = $this->postJson('/api/user/create', [
            'name' => 'morteza',
            'phone_number' => '09119816511',
            'password' => '12345678',
        ]);

        $token = explode('|', $response->json()['data']['token'])[1];

        // test fail logout with wrong logout
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}1",
            'Accept' => 'application/json'
        ])->postJson('/api/user/logout');
        $response->assertStatus(401);

        // test success logout
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json'
        ])->postJson('/api/user/logout');

        $response->assertStatus(200);
        $this->assertTrue($response->json()['data']['message'] == 'کاربر با موفقیت خارج شد.');
    }
}

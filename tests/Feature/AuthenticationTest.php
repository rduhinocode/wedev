<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function testUserRegistration()
    {
        $faker = \Faker\Factory::create();

        $this->json('POST', url('api/register'),
        [
            'first_name'=> $faker->firstName,
            'last_name' => $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'password' => 'password',
            'user_details' => [
                'address' => $faker->address,
                'country' => $faker->country
            ]
        ])->assertStatus(200)->assertJsonStructure([
            'success',
            'message'
        ]);
    }

    public function testFailUserRegistration()
    {
        $faker = \Faker\Factory::create();

        $this->json('POST', url('api/register'),
            [
                'first_name'=> $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'password' => 'password',

            ])->assertStatus(422)->assertJson(function (AssertableJson $json) {
                $json->has("message")
                    ->has("errors");
            });
    }

    public function testLoginUser()
    {
        $user = User::factory()->create();

        $this->json('POST', url('api/login'),
            [
                'email'=> $user->email,
                'password' => 'password',

            ])->assertStatus(200)->assertJson(function (AssertableJson $json) {
                $json->has('access_token')
                    ->has('token_type')
                    ->has('user_data');
        });
    }

    public function testFailLoginRegistration()
    {
        $user = User::factory()->create();

        $this->json('POST', url('api/login'),
            [
                'email'=> $user->email,
                'password' => 'passwordextended',
            ])->assertStatus(401)->assertJson(function (AssertableJson $json) {
                $json->has('message');
        });
    }
}

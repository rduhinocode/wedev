<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    public function testResponseStructure()
    {
        $user = $this->createUsers();

        $response = $this->actingAs($user)->get('/api/users');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'details' => [
                        'id',
                        'address',
                        'country',
                    ]
                ]
            ],
            'links' => [
                'first',
                'last',
                'next',
                'prev',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total'
            ]
        ]);
    }

    public function testListWithoutFilters()
    {
        $user = $this->createUsers();

        $response = $this->actingAs($user)->get('/api/users');
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function testListPagination()
    {
        $user = $this->createUsers(20);

        $response = $this->actingAs($user)->get('/api/users?page=1&perPage=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');

    }

    public function createUsers($count = 3) {
        $user = null;
        User::factory($count)->make()->each(function (User $userf) use(&$user){
            $userf->save();

            $user = $userf;
            $userf->details()->save(UserDetails::factory()->make());
        });

        return $user;
    }
}

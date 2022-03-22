<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDetails;
use Tests\TestCase;

class UserListTest extends TestCase
{
    public function testResponseStructure()
    {
        $user = null;
        User::factory(3)->make()->each(function (User $userf) use(&$user){
            $userf->save();

            $user = $userf;
            $userf->details()->save(UserDetails::factory()->make());
        });

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
}

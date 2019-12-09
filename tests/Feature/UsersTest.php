<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersTest extends TestCase 
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_a_user_as_a_resource_object()
    {
        // $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->getJson("/api/v1/users/{$user->id}", [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'type' => 'users',
                'attributes' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->toJSON(),
                    'updated_at' => $user->updated_at->toJSON(),
                ]
            ]
        ]);
    }

    /** @test */
    public function it_returns_all_users_as_a_collection_of_resource_objects()
    {
        $users = factory(User::class, 3)->create();
        // $users = $users->sortBy(function ($item) {
        //     return $item->id;
        // })->values();

        Passport::actingAs($users->first());

        $this->getJson("/api/v1/users", [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)
        ->assertJson([
            "data" => [
                [
                    "id" => $users[0]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'email' => $users[0]->email,
                        'role' => 'user',
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[1]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'email' => $users[1]->email,
                        'role' => 'user',
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[2]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'email' => $users[2]->email,
                        'role' => 'user',
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    /** @test */
    public function it_can_create_a_user_from_a_resource_object()
    {
        $this->withoutExceptionHandling();
        // Check that we can create a user
        // We need to hash the password in our controller before we save the user.
        // Check that the password is being hashed.
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/users', [
            'data' => [
                'type' => 'users',
                'attributes' => [
                   'name' => 'John Doe',
                   'email' => 'john@example.com',
                   'password' => 'secret',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(201)
             ->assertJson([
                 "data" => [
                     "type" => "users",
                     "attributes" => [
                         'name' => 'John Doe',
                         'email' => 'john@example.com',
                         'role' => 'user',
                         'created_at' => now()->setMilliseconds(0)->toJSON(),
                         'updated_at' => now() ->setMilliseconds(0)->toJSON(),
                     ]
                 ]
             ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'user',
        ]);
        
        // Test the password hash.
        // We need to hash the password in our controller before we save the user
        $this->assertTrue(Hash::check('secret', User::whereName('John Doe')->first()->password));
    }

    /** @test */
    public function it_can_update_a_user_from_a_resource_object()
    {
        // $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->patchJson("/api/v1/users/{$user->id}", [
            'data' => [
                'id' => $user->id,
                'type' => 'users',
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => 'secret',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(200)
        ->assertJson([
            "data" => [
                "id" => $user->id,
                "type" => "users",
                "attributes" => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'created_at' => now()->setMilliseconds(0)->toJSON(),
                    'updated_at' => now() ->setMilliseconds(0)->toJSON(),
                ]
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertTrue(Hash::check('secret', User::whereId($user->id)->first()->password));
    }

    /** @test */
    public function it_validates_that_the_type_member_is_given_when_creating_a_user()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/users', [
            'data' => [
                'type' => '',
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => 'secret',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(422)
        ->assertJson([
            'errors' => [
                [
                    'title'   => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source'  => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function it_can_delete_a_user_through_a_delete_request()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->delete("/api/v1/users/{$user->id}",[], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

}
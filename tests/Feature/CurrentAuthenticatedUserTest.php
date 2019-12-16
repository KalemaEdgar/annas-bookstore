<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CurrentAuthenticatedUserTest extends TestCase 
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_the_current_authenticated_user_as_a_resource_object()
    {
        // Ensures that the current authenticated user route also adheres to the JSON:API specification.
        // $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->get('/api/v1/users/current', [
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

}
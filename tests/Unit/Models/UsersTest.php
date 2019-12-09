<?php

namespace Tests\Unit\Models;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersTest extends TestCase 
{
    use DatabaseMigrations;

    /** @test */
    public function a_users_ID_is_a_UUID_instead_of_an_integer()
    {
        // For security, we want to create the id for a new user as a UUID instead of the normal integer ids from the database
        $user = factory(User::class)->create();
        $this->assertFalse(is_integer($user->id)); // UUIDs are not integers
        $this->assertEquals(36, strlen($user->id)); // UUIDs always have a length of 36
    }

    /** 
     * @test
     * Check that the role attribute is on the model
     * Check that the role attribute contains a "user" value as the default
     */
    public function it_has_a_role_of_user_by_default()
    {
        $this->withoutExceptionHandling();
        
        $user = factory(User::class)->create();
        $this->assertEquals('user', $user->role);
    }

}
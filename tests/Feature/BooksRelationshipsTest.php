<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

// Contains the API relationships implementations for our books resource.
class BooksRelationshipsTest extends TestCase 
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_a_relationship_to_authors_adhering_to_json_api_spec()
    {
        // 1. We set up our world
            // a. We need a book to be able to get it through our API
            // b. We need a couple of authors to exist to be able to add them as author for our book
            // c. We need to be authenticated
        // 2. We run the code we are testing here
            // a. We make a GET request to the right API endpoint
            // b. We add the right Accept and Content-Type headers
        // 3. We assert against the result that
            // a. We get a status code 200 OK back
            // b. We see the relationships member
            // c. We see the authors relationship
            // d. We see the links member inside the relationship
            // e. We see the resource linkage inside the relationship
            // f. We see the resource identifier objects for the authors
        
        

    }
}
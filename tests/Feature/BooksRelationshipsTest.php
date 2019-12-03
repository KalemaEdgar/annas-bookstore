<?php

namespace Tests\Feature;

use App\Author;
use App\Book;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;

// Contains the API relationships implementations for our books resource.
class BooksRelationshipsTest extends TestCase 
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_a_relationship_to_authors_adhering_to_json_api_spec()
    {
        $this->withoutExceptionHandling();
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
        
        $book = factory(Book::class)->create();
        $authors = factory(Author::class, 3)->create();
        $book->authors()->sync($authors->pluck('id'));
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        
        $this->getJson('/api/v1/books/1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => '1',
                'type' => 'books',
                'relationships' => [
                    'authors' => [
                        'links' => [
                            'self' => route('books.relationships.authors', ['book' => $book->id]),
                            'related' => route('books.authors', ['book' => $book->id]),
                        ],
                        'data' => [
                            [
                                'id' => $authors->get(0)->id,
                                'type' => 'authors'
                            ],
                            [
                                'id' => $authors->get(1)->id,
                                'type' => 'authors'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // dd(json_decode($response->getContent()));
    }

}
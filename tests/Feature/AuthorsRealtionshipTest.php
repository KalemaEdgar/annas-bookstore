<?php

namespace Tests\Feature;

use App\Author;
use App\Book;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AuthorsRelationshipTest extends TestCase 
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_a_relationship_to_books_adhering_to_json_api_spec()
    {
        $this->withoutExceptionHandling();
               
        $author = factory(Author::class)->create();
        $books = factory(Book::class, 3)->create();
        $author->books()->sync($books->pluck('id'));
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        
        $this->getJson('/api/v1/authors/1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => '1',
                'type' => 'authors',
                'relationships' => [
                    'books' => [
                        'links' => [
                            'self' => route('authors.relationships.books', ['author' => $author->id]),
                            'related' => route('authors.books', ['author' => $author->id]),
                        ],
                        'data' => [
                            [
                                'id' => $books->get(0)->id,
                                'type' => 'books'
                            ],
                            [
                                'id' => $books->get(1)->id,
                                'type' => 'books'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // dd(json_decode($response->getContent()));
    }
}
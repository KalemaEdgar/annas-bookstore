<?php

namespace Tests\Feature;

use App\Book;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;

class BooksTest extends TestCase 
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_a_book_as_a_resource_object()
    {
        // This test checks if we can get a book from the endpoint
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $book = factory(Book::class)->create();

        $this->getJson('/api/v1/books/1', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => '1',
                'type' => 'books',
                'attributes' => [
                    'title' => $book->title,
                    'description' => $book->description,
                    'publication_year' => $book->publication_year,
                    'created_at' => $book->created_at->toJSON(),
                    'updated_at' => $book->updated_at->toJSON(),
                ]
            ]
        ]);
    }

    /** @test */
    public function it_returns_all_books_as_a_collection_of_resource_objects()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $book = factory(Book::class, 2)->create();

        $this->getJson('/api/v1/books', [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                [
                    'id' => '1',
                    'type' => 'books',
                    'attributes' => [
                        'title' => $book[0]->title,
                        'description' => $book[0]->description,
                        'publication_year' => $book[0]->publication_year,
                        'created_at' => $book[0]->created_at->toJSON(),
                        'updated_at' => $book[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    'id' => '2',
                    'type' => 'books',
                    'attributes' => [
                        'title' => $book[1]->title,
                        'description' => $book[1]->description,
                        'publication_year' => $book[1]->publication_year,
                        'created_at' => $book[1]->created_at->toJSON(),
                        'updated_at' => $book[1]->updated_at->toJSON(),
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_create_a_book_from_a_resource_object()
    {
        // Need a user for authentication
        // No need to create a book from a factory since we are testing that creation endpoint
        // Check that a POST request is sent to the endpoint and a book is created
        // Assert that the returned status is 201 Created
        // Check that the entry has been created in the database
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => 'books',
                'attributes' => [
                    'title' => 'Kedz Book',
                    'description' => 'A book about healing',
                    'publication_year' => '2019',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(201)
        ->assertJson([
            'data' => [
                'id' => '1',
                'type' => 'books',
                'attributes' => [
                    'title' => 'Kedz Book',
                    'description' => 'A book about healing',
                    'publication_year' => '2019',
                    'created_at' => now()->setMilliseconds(0)->toJSON(),
                    'updated_at' => now()->setMilliseconds(0)->toJSON(),
                ]
            ]
        ])->assertHeader('Location', url('/api/v1/books/1'));

        // dd(json_decode($response->getContent()));

        $this->assertDatabaseHas('books', [
            'id' => 1,
            'title' => 'Kedz Book',
            'description' => 'A book about healing',
            'publication_year' => '2019',
        ]);
    }

    /** @test */
    public function it_validates_that_the_type_member_is_given_when_creating_a_book() 
    {
        // We need an invalid request object for the creation of our book by either leaving out a member or setting it with the wrong datatype.
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => '',
                'attributes' => [
                    'title' => 'Building an API with Laravel',
                    'description' => 'A book about API development',
                    'publication_year' => '2019',
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
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseMissing('books', [
            'id' => 1,
            'title' => 'Building an API with Laravel',
            'description' => 'A book about API development',
            'publication_year' => '2019',
        ]);
    }

    /** @test */
    public function it_validates_that_the_type_member_has_the_value_of_books_when_creating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => 'booo',
                'attributes' => [
                    'title' => 'Building an API with Laravel',
                    'description' => 'A book about API development',
                    'publication_year' => '2019',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)
        ->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);
        
        $this->assertDatabaseMissing('books', [
            'id' => 1,
            'title' => 'Building an API with Laravel',
            'description' => 'A book about API development',
            'publication_year' => '2019',
        ]);
    }

    /** @test */
    public function it_validates_that_the_attributes_member_has_been_given_when_creating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => 'books',
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)
        ->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes field is required.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ]
                ]
            ]
        ]);
    }
    
    /** @test */
    public function it_validates_that_the_attributes_member_is_an_object_given_when_creating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => 'books',
                'attributes' => 'this is not an object'
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(422)
        ->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes must be an array.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_validates_that_a_title_attribute_is_given_when_creating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => 'books',
                'attributes' => [
                    'description' => 'A book about API development',
                    'publication_year' => '2019',
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
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.title field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/title',
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_validates_that_a_title_attribute_is_a_string_when_creating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        
        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => 'books',
                'attributes' => [
                    'title' => 42,
                    'description' => 'A book about API development',
                    'publication_year' => '2019',
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
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.title must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/title',
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_validates_that_a_description_attribute_is_given_when_creating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        
        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => 'books',
                'attributes' => [
                    'title' => 'Building an API with Laravel',
                    'publication_year' => '2019',
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
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.description field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/description',
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_validates_that_a_description_attribute_is_a_string_when_creating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => 'books',
                'attributes' => [
                    'title' => 'Building an API with Laravel',
                    'description' => 42,
                    'publication_year' => '2019',
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
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.description must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/description',
                    ]
                ]
            ]
        ]);
    }
    
    /** @test */
    public function it_validates_that_a_publication_year_attribute_is_given_when_creating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => 'books',
                'attributes' => [
                    'title' => 'Building an API with Laravel',
                    'description' => 'A book about API development',
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
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.publication year field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/publication_year',
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_validates_that_a_publication_year_attribute_is_a_string_when_creating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/books', [
            'data' => [
                'type' => 'books',
                'attributes' => [
                    'title' => 'Building an API with Laravel',
                    'description' => 'A book about API development',
                    'publication_year' => 2019,
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
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.publication year must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/publication_year',
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_update_a_book_from_a_resource_object()
    {
        Passport::actingAs(factory(User::class)->create());
        // We need our book to be created so we can update it with new data
        $book = factory(Book::class)->create();

        // $response = $this->patchJson('/api/v1/books/1', [
        $this->patchJson('/api/v1/books/1', [
            'data' => [
                'id' => '1',
                'type' => 'books',
                'attributes' => [
                    'title' => 'Keds book update',
                    'description' => 'A book for the API lovers',
                    'publication_year' => '2020',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => '1',
                'type' => 'books',
                'attributes' => [
                    'title' => 'Keds book update',
                    'description' => 'A book for the API lovers',
                    'publication_year' => '2020',
                    'created_at' => now()->setMilliseconds(0)->toJSON(),
                    'updated_at' => now()->setMilliseconds(0)->toJSON(),
                ]
            ]
        ]);

        $this->assertDatabaseHas('books', [
            'id' => 1,
            'title' => 'Keds book update',
            'description' => 'A book for the API lovers',
            'publication_year' => '2020',
        ]);

        // dd(json_decode($response->getContent()));
    }

    /** @test */
    public function it_validates_that_an_id_member_is_given_when_updating_an_book()
    {
        Passport::actingAs(factory(User::class)->create());
        // We need our book to be created so we can update it with new data
        $book = factory(Book::class)->create();

        // Miss out the id attribute since that is what we are testing
        $this->patchJson('/api/v1/books/1', [
            'data' => [
                'type' => 'books',
                'attributes' => [
                    'title' => 'Keds book update',
                    'description' => 'A book for the API lovers',
                    'publication_year' => '2020',
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
                    'title' => 'Validation Error',
                    'details' => 'The data.id field is required.',
                    'source' => [
                        'pointer' => '/data/id',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('books', [
            'id' => '1',
            'title' => $book->title,
        ]);
    }

    /** @test */
    public function it_validates_that_an_id_member_is_a_string_when_updating_an_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $book = factory(Book::class)->create();

        $this->patchJson('/api/v1/books/1', [
            'data' => [
                'id' => 1,
                'type' => 'books',
                'attributes' => [
                    'title' => 'Building an API with Laravel',
                    'description' => 'A book about API development',
                    'publication_year' => '2019',
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
                    'title' => 'Validation Error',
                    'details' => 'The data.id must be a string.',
                    'source' => [
                        'pointer' => '/data/id',
                    ]
                ]
            ]
        ]);
        
        $this->assertDatabaseHas('books', [
            'id' => 1,
            'title' => $book->title,
        ]);
    }

    /** @test */
    public function it_validates_that_the_type_member_is_given_when_updating_an_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $book = factory(Book::class)->create();
        
        $this->patchJson('/api/v1/books/1', [
            'data' => [
                'id' => '1',
                'attributes' => [
                    'title' => 'Building an API with Laravel',
                    'description' => 'A book about API development',
                    'publication_year' => '2019',
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
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('books', [
            'id' => 1,
            'title' => $book->title,
        ]);
    }

    /** @test */
    public function it_validates_that_the_type_member_has_the_value_of_books_when_updating_an_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $book = factory(Book::class)->create();

        $this->patchJson('/api/v1/books/1', [
            'data' => [
                'id' => '1',
                'type' => 'booo',
                'attributes' => [
                    'title' => 'Building an API with Laravel',
                    'description' => 'A book about API development',
                    'publication_year' => '2019',
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
                    'title' => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source' => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('books', [
            'id' => 1,
            'title' => $book->title,
        ]);
    }

    /** @test */
    public function it_validates_that_the_attributes_member_has_been_given_when_updating_an_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $book = factory(Book::class)->create();

        $this->patchJson('/api/v1/books/1', [
            'data' => [
                'id' => '1',
                'type' => 'books',
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(422)
        ->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes field is required.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('books', [
            'id' => 1,
            'title' => $book->title,
        ]);
    }

    /** @test */
    public function it_validates_that_the_attributes_member_is_an_object_given_when_updating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $book = factory(Book::class)->create();

        $this->patchJson('/api/v1/books/1', [
            'data' => [
                'id' => '1',
                'type' => 'books',
                'attributes' => 'this is not an object'
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(422)
        ->assertJson([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes must be an array.',
                    'source' => [
                        'pointer' => '/data/attributes',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('books', [
            'id' => 1,
            'title' => $book->title,
        ]);
    }

    /** @test */
    public function it_validates_that_a_title_attribute_is_a_string_when_updating_an_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $book = factory(Book::class)->create();

        $this->patchJson('/api/v1/books/1', [
            'data' => [
                'id' => '1',
                'type' => 'books',
                'attributes' => [
                    'title' => 42,
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
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.title must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/title',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('books', [
            'id' => 1,
            'title' => $book->title,
        ]);
    }

    /** @test */
    public function it_validates_that_a_description_attribute_is_a_string_when_updating_an_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $book = factory(Book::class)->create();
        
        $this->patchJson('/api/v1/books/1', [
            'data' => [
                'id' => '1',
                'type' => 'books',
                'attributes' => [
                    'description' => 42,
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
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.description must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/description',
                    ]
                ]
            ]
        ]);
        
        $this->assertDatabaseHas('books', [
            'id' => 1,
            'title' => $book->title,
        ]);
    }
    
    /** @test */
    public function it_validates_that_a_publication_year_attribute_is_a_string_when_updating_a_book()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $book = factory(Book::class)->create();
        
        $this->patchJson('/api/v1/books/1', [
            'data' => [
                'id' => '1',
                'type' => 'books',
                'attributes' => [
                    'publication_year' => 2019,
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
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.publication year must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/publication_year',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('books', [
            'id' => 1,
            'title' => $book->title,
        ]);
    }
    
    /** @test */
    public function it_can_delete_a_book_through_a_delete_request() 
    {
        Passport::actingAs(factory(User::class)->create());
        $book = factory(Book::class)->create();

        $this->delete('/api/v1/books/1', [], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        $this->assertDatabaseMissing('books', [
            'id' => 1,
            'title' => $book->title,
        ]);
    }

}
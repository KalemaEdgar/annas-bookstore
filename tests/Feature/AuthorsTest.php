<?php

namespace Tests\Feature;

use App\Author;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthorsTest extends TestCase
{

    use DatabaseMigrations;

    /**
     * Method to test if we can get a single resource or author from the API
     * Make a GET request to the route, using an ID of an author
     * then assert that we get that author back as a correct JSON:API specification resource object.
     * 
     * Basically, we are testing the show method in the AuthorsController since it provides that functionality
     * @test
     */
    public function it_returns_an_author_as_a_resource_object()
    {
        // Create an author for testing. We need to use the id generated to send to the API endpoint
        $author = factory(Author::class)->create(); // Returns a single model

        // We need to make an authenticated request to test the API. Otherwise, you get 401 Unauthorised
        // Create a user that we can use to authenticate the requests since Passport requires a User to do a request
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        // Make a request to our endpoint to fetch data
        // The second argument in the getJson method lets us supply the headers for the request in an array.
        $this->getJson('/api/v1/authors/1', [
                'accept' => 'application/vnd.api+json',
                'content-type' => 'application/vnd.api+json',
            ])
            ->assertStatus(200) // Check if the response status code is 200 which means SUCCESSFUL
            ->assertJson([ // Check if the returned response format is as expected
                "data" => [
                    "id" => '1',
                    "type" => "authors",
                    "attributes" => [
                        'name' => $author->name,
                        'created_at' => $author->created_at->toJSON(),
                        'updated_at' => $author->updated_at->toJSON(),
                    ]
                ]
            ]);
    }

    /**
     * Method to test if we can get a collection of authors from the API endpoint
     * and that the collection of authors is a collection of resource objects.
     * Make a GET request
     * 
     * We are testing the index method in the AuthorsController since it provides that functionality
     * @test
     */
    public function it_returns_all_authors_as_a_collection_of_resource_objects()
    {
        // Get a user for authentication
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        // Create a collection of authors that need to be returned
        $authors = factory(Author::class, 3)->create(); // Returns a collection of author model since we created 3 authors

        // $this->getJson('/api/v1/authors') // What is the difference with getJson() and just using get(). Both are working fine
        // $response = $this->get('/api/v1/authors')
        $this->get('/api/v1/authors', [
                'accept' => 'application/vnd.api+json',
                'content-type' => 'application/vnd.api+json',
            ])
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    [
                        "id" => '1',
                        "type" => "authors",
                        "attributes" => [
                            'name' => $authors[0]->name,
                            'created_at' => $authors[0]->created_at->toJSON(),
                            'updated_at' => $authors[0]->updated_at->toJSON(),
                        ]
                    ],
                    [
                        "id" => '2',
                        "type" => "authors",
                        "attributes" => [
                            'name' => $authors[1]->name,
                            'created_at' => $authors[1]->created_at->toJSON(),
                            'updated_at' => $authors[1]->updated_at->toJSON(),
                        ]
                    ],
                    [
                        "id" => '3',
                        "type" => "authors",
                        "attributes" => [
                            'name' => $authors[2]->name,
                            'created_at' => $authors[2]->created_at->toJSON(),
                            'updated_at' => $authors[2]->updated_at->toJSON(),
                        ]
                    ],
                ]
            ]);

            // dd(json_decode($response->getContent()));
    }

    /**
     * Method to test if we can create a new author from a resource object
     * Make a POST request with the necessary data and then 
     * - assert that we get the correct status code (201 Created).
     * - assert that the location header is a part of the response as well.
     * - assert that the correct resource object is responded back to the client.
     * - assert that the new author has been added to the database successfully.
     * 
     * We are testing the store method in the AuthorsController
     * @test
     */
    public function it_can_create_an_author_from_a_resource_object()
    {
        // Setup a user to make authenticated requests
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/authors', [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => 'Kalema Edgar'
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(201) // Check if the resource has been added and correct status sent back
        ->assertHeader('Location', url('/api/v1/authors/1')) // Check if the response has the Location header to this resource
        ->assertJson([ // Check if the returned response format is as expected
            "data" => [
                "id" => '1',
                "type" => "authors",
                "attributes" => [
                    'name' => 'Kalema Edgar',
                    'created_at' => now()->setMilliseconds(0)->toJSON(),
                    'updated_at' => now()->setMilliseconds(0)->toJSON(),
                ]
            ]
        ]);
        
        // Check if the entry has been added to the database.
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => 'Kalema Edgar'
        ]);
    }

    /**
     * Test to ensure the validation rules are working when creating an author
     * Make a POST request with the a mandatory attribute missing like "type"
     * - assert that we get the correct status code (422).
     * - Validate that we receive an object in the data member.
     * - Validate the type of the resource object, both that it is actually a part of the resource object, but also that the value is authors.
     * - Assert against what is being returned from the API. We would like a correct error form that adheres to the conventions of the JSON:API specification
     * 
     * @test
     */
    public function it_validates_that_the_type_member_is_given_when_creating_an_author()
    {
        // Setup a user to make authenticated requests
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        // To simulate the validation error, send a POST request with the type attribute missing. (The "Type" is mandatory to the API)
        // Check the rules under app/Http/Requests/CreateAuthorRequest.php
        $this->postJson('/api/v1/authors', [
            'data' => [
                'type' => '',
                'attributes' => [
                    'name' => 'Kalema Edgar'
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(422)
        ->assertJson([ // The invalidJson function under app\Exceptions\Handler.php deals with formatting the response as we want it
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
        
        $this->assertDatabaseMissing('authors', [
            'id' => 1,
            'name' => 'Kalema Edgar'
        ]);
    }

    /**
     * Test to ensure that the request has a value of authors for the type attribute
     * @test
     */
    public function it_validates_that_the_type_member_has_the_value_of_authors_when_creating_an_author()
    {
        // Setup a user to make authenticated requests
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        // To simulate the validation error, send a POST request with the type attribute value incorrect.
        // Check the rules under app/Http/Requests/CreateAuthorRequest.php
        $this->postJson('/api/v1/authors', [
            'data' => [
                'type' => 'authaaazr', // Sending an incorrect value for type attribute (authaaazr instead of authors)
                'attributes' => [
                    'name' => 'Kalema Edgar'
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
        
        $this->assertDatabaseMissing('authors', [
            'id' => 1,
            'name' => 'Kalema Edgar'
        ]);
    }

    /**
     * @test
     */
    public function it_validates_that_the_attributes_member_has_been_given_when_creating_an_author()
    {
        // Setup a user to make authenticated requests
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        
        $this->postJson('/api/v1/authors', [
            'data' => [
                'type' => 'authors',
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
        
        $this->assertDatabaseMissing('authors', [
            'id' => 1,
            'name' => 'Kalema Edgar'
        ]);
    }

    /**
     * @test
     */
    public function it_validates_that_the_attributes_member_is_an_object_given_when_creating_an_author()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        
        $this->postJson('/api/v1/authors', [
            'data' => [
                'type' => 'authors',
                'attributes' => 'not an object',
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
        
        $this->assertDatabaseMissing('authors', [
            'id' => 1,
            'name' => 'Kalema Edgar'
        ]);
    }

    /**
     * Validate that the name attribute is required
     * @test
     */
    public function it_validates_that_a_name_attribute_is_given_when_creating_an_author()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        
        $this->postJson('/api/v1/authors', [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => ''
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
                    'details' => 'The data.attributes.name field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/name',
                    ]
                ]
            ]
        ]);
        
        $this->assertDatabaseMissing('authors', [
            'id' => 1,
            'name' => 'Kalema Edgar'
        ]);
    }

    /**
     * Validate that the name attribute is a string
     * We give an integer value in the name member instead of a string and assert that the validation catches this.
     * @test
     */
    public function it_validates_that_a_name_attribute_is_a_string_when_creating_an_author()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        
        $this->postJson('/api/v1/authors', [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => 47
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
                    'details' => 'The data.attributes.name must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/name',
                    ]
                ]
            ]
        ]);
        
        $this->assertDatabaseMissing('authors', [
            'id' => 1,
            'name' => 'Kalema Edgar'
        ]);
    }

    /**
     * Method to test if we can update an author from a resource object
     * Make a POST request with the necessary data and then 
     * - assert that we get the correct status code (200 OK).
     * - assert that the updated resource object is responded back to the client.
     * - assert that the author has been updated in the database successfully.
     * 
     * We are testing the store method in the AuthorsController
     * @test
     */
    public function it_can_update_an_author_from_a_resource_object()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $author = factory(Author::class)->create();

        $creationTimestamp = now();
        sleep(1);

        $this->patchJson('/api/v1/authors/1', [
            'data' => [
                'id' => '1', // Needs to be a string to conform with the JSON:API standard. Making it integer causes it to fail
                'type' => 'authors',
                'attributes' => [
                    'name' => 'Josephine Balungi'
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(200)
        ->assertJson([ // Check that the updated data is sent back to the client
            "data" => [
                "id" => '1',
                "type" => "authors",
                "attributes" => [
                    'name' => 'Josephine Balungi',
                    'created_at' => $creationTimestamp->setMilliseconds(0)->toJSON(),
                    'updated_at' => now()->setMilliseconds(0)->toJSON(),
                ]
            ]
        ]);

        // Check that the data has been updated in the database
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => 'Josephine Balungi'
        ]);
    }

    /**
     * @test
     */
    public function it_validates_that_an_id_member_is_given_when_updating_an_author()
    {
        // We are doing PATCH request instead of a POST request
        // Which means we will hit the app/Http/Requests/UpdateAuthorRequest.php rules instead, making it possible to test the rules.
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $author = factory(Author::class)->create();

        $this->patchJson('/api/v1/authors/1', [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => 'Josephine Balungi'
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

        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name,
        ]);

    }

    /**
     * @test
     */
    public function it_validates_that_a_id_member_is_a_string_when_updating_an_author()
    {
        // Need a user for authentication
        // Need an author for updating
        // Simulate an update request where the id is not a string but an integer
        // Assert that the validation fails and also proper response sent back to the user
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $author = factory(Author::class)->create();

        $this->patchJson('/api/v1/authors/1', [
            'data' => [
                'id' => 1,
                'type' => 'authors',
                'attributes' => [
                    'name' => 'Kalema Edgar'
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

        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name,
        ]);
    }

    /** 
     * @test
     */
    public function it_validates_that_the_type_member_is_given_when_updating_an_author()
    {
        // Setup a user to make authenticated requests
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $author = factory(Author::class)->create();
        
        $this->patchJson('/api/v1/authors/1', [
            'data' => [
                'id' => '1',
                'type' => '',
                'attributes' => [
                    'name' => 'Kalema Edgar'
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
        
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /** 
     * @test
     */
    public function it_validates_that_the_type_member_has_the_value_of_authors_when_updating_an_author()
    {
        // Setup a user to make authenticated requests
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $author = factory(Author::class)->create();
        
        $this->patchJson('/api/v1/authors/1', [
            'data' => [
                'id' => '1',
                'type' => 'authaaax',
                'attributes' => [
                    'name' => 'Kalema Edgar'
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
        
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /** 
     * @test
     */
    public function it_validates_that_the_attributes_member_has_been_given_when_updating_an_author()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $author = factory(Author::class)->create();
        
        $this->patchJson('/api/v1/authors/1', [
            'data' => [
                'id' => '1',
                'type' => 'authors',
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
        
        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /**
     * @test
     */
    public function it_validates_that_the_attributes_member_is_an_object_given_when_updating_an_author()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $author = factory(Author::class)->create();

        $this->patchJson('/api/v1/authors/1', [
            'data' => [
                'id' => '1',
                'type' => 'authors',
                'attributes' => 'not an object',
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

        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name,
        ]);
    }

    /**
     * @test
     */
    public function it_validates_that_the_name_attribute_is_a_string_when_updating_an_author()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);
        $author = factory(Author::class)->create();

        $this->patchJson('/api/v1/authors/1', [
            'data' => [
                'id' => '1',
                'type' => 'authors',
                'attributes' => [
                    'name' => 123
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
                    'details' => 'The data.attributes.name must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/name',
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('authors', [
            'id' => 1,
            'name' => $author->name,
        ]);

    }

    /**
     * Method to test if we can delete an author
     * Make a DELETE request - (no data required to be passed in the request) and then 
     * - assert that we get the correct status code (204 No Content).
     * - assert that the author has been deleted from the database successfully.
     * 
     * We are testing the delete method in the AuthorsController
     * @test
     */
    public function it_can_delete_an_author_through_a_delete_request()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $author = factory(Author::class)->create();

        $this->delete('/api/v1/authors/1', [], [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        // Check that the data has been deleted from the database
        $this->assertDatabaseMissing('authors', [
            'id' => 1,
            'name' => $author->name
        ]);
    }

    /**
     * I didnot implement this since as of now, the spatie/laravel-query-builder doesnot support Laravel 6 
     * @test */
    // public function it_can_sort_authors_by_name_through_a_sort_query_parameter()
    // {
    //     // Get a user for authentication
    //     $user = factory(User::class)->create();
    //     Passport::actingAs($user);

    //     $authors = collect([
    //         'Bertram',
    //         'Claus',
    //         'Anna',
    //     ])->map(function($name) {
    //         return factory(Author::class)->create([
    //             'name' => $name
    //         ]);
    //     });
            
    //     $this->get('/api/v1/authors?sort=name', [
    //         'accept' => 'application/vnd.api+json',
    //         'content-type' => 'application/vnd.api+json',
    //     ])
    //     ->assertStatus(200)
    //     ->assertJson([
    //         "data" => [
    //             [
    //                 "id" => '3',
    //                 "type" => "authors",
    //                 "attributes" => [
    //                     'name' => 'Anna',
    //                     'created_at' => $authors[2]->created_at->toJSON(),
    //                     'updated_at' => $authors[2]->updated_at->toJSON(),
    //                 ]
    //             ],
    //             [
    //                 "id" => '1',
    //                 "type" => "authors",
    //                 "attributes" => [
    //                     'name' => 'Bertram',
    //                     'created_at' => $authors[0]->created_at->toJSON(),
    //                     'updated_at' => $authors[0]->updated_at->toJSON(),
    //                 ]
    //             ],
    //             [
    //                 "id" => '2',
    //                 "type" => "authors",
    //                 "attributes" => [
    //                     'name' => 'Claus',
    //                     'created_at' => $authors[1]->created_at->toJSON(),
    //                     'updated_at' => $authors[1]->updated_at->toJSON(),
    //                 ]
    //             ],
    //         ]
    //     ]);
    // }

    /** 
     * @test
     * Test for sorting in descending order
     */
    // public function it_can_sort_authors_by_name_in_descending_order_through_a_sort_query_parameter()
    // {
    //     $user = factory(User::class)->create();
    //     Passport::actingAs($user);
    //     $authors = collect([
    //         'Bertram',
    //         'Claus',
    //         'Anna',
    //     ])->map(function($name){
    //         return factory(Author::class)->create([
    //             'name' => $name
    //         ]);
    //     });

    //     // Add a minus in front of our name value in the query parameter to convey that we want to sort in descending order.
    //     $this->get('/api/v1/authors?sort=-name', [
    //         'accept' => 'application/vnd.api+json',
    //         'content-type' => 'application/vnd.api+json',
    //     ])
    //     ->assertStatus(200)
    //     ->assertJson([
    //         "data" => [
    //             [
    //                 "id" => '2',
    //                 "type" => "authors",
    //                 "attributes" => [
    //                     'name' => 'Claus',
    //                     'created_at' => $authors[1]->created_at->toJSON(),
    //                     'updated_at' => $authors[1]->updated_at->toJSON(),
    //                 ]
    //             ],
    //             [
    //                 "id" => '1',
    //                 "type" => "authors",
    //                 "attributes" => [
    //                     'name' => 'Bertram',
    //                     'created_at' => $authors[0]->created_at->toJSON(),
    //                     'updated_at' => $authors[0]->updated_at->toJSON(),
    //                 ]
    //             ],
    //             [
    //                 "id" => '3',
    //                 "type" => "authors",
    //                 "attributes" => [
    //                     'name' => 'Anna',
    //                     'created_at' => $authors[2]->created_at->toJSON(),
    //                     'updated_at' => $authors[2]->updated_at->toJSON(),
    //                 ]
    //             ],
    //         ]
    //     ]);
    // }

}
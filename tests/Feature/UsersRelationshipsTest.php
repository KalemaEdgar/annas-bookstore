<?php


namespace Tests\Feature;


use App\Comment;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersRelationshipsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    // public function it_returns_a_relationship_to_comments_adhering_to_json_api_spec()
    // {
    //     $this->withoutExceptionHandling();
    //     $user = factory(User::class)->create();
    //     Passport::actingAs($user);

    //     $comments = factory(Comment::class, 3)->make();
    //     $user->comments()->saveMany($comments);


    //     $this->getJson("/api/v1/users/{$user->id}?include=comments", [
    //         'accept' => 'application/vnd.api+json',
    //         'content-type' => 'application/vnd.api+json',
    //     ])
    //     ->assertStatus(200)
    //     ->assertJson([
    //         "data" => [
    //             "id" => $user->id,
    //             "type" => "users",
    //             "attributes" => [
    //                 'name' => $user->name,
    //                 'email' => $user->email,
    //                 'created_at' => $user->created_at->toJSON(),
    //                 'updated_at' => $user->updated_at->toJSON(),
    //             ],
    //             'relationships' => [
    //                 'comments' => [
    //                     'links' => [
    //                         'self' => route(
    //                             'users.relationships.comments',
    //                             ['id' => $user->id]
    //                         ),
    //                         'related' => route(
    //                             'users.comments',
    //                             ['id' => $user->id]
    //                         ),
    //                     ],
    //                     'data' => [
    //                         [
    //                             'id' => $comments->get(0)->id,
    //                             'type' => 'comments'
    //                         ],
    //                         [
    //                             'id' => $comments->get(1)->id,
    //                             'type' => 'comments'
    //                         ],
    //                         [
    //                             'id' => $comments->get(2)->id,
    //                             'type' => 'comments'
    //                         ]
    //                     ]
    //                 ]
    //             ]
    //         ],
    //         'included' => [
    //             [
    //                 'id' => '1',
    //                 'type' => 'comments',
    //                 'attributes' => [
    //                     'message' => $comments->get(0)->message,
    //                     'created_at' => $comments->get(0)->created_at->toJson(),
    //                     'updated_at' => $comments->get(0)->updated_at->toJson(),
    //                 ]
    //             ],
    //             [
    //                 'id' => '2',
    //                 'type' => 'comments',
    //                 'attributes' => [
    //                     'message' => $comments->get(1)->message,
    //                     'created_at' => $comments->get(1)->created_at->toJson(),
    //                     'updated_at' => $comments->get(1)->updated_at->toJson(),
    //                 ]
    //             ],
    //             [
    //                 'id' => '3',
    //                 'type' => 'comments',
    //                 'attributes' => [
    //                     'message' => $comments->get(2)->message,
    //                     'created_at' => $comments->get(2)->created_at->toJson(),
    //                     'updated_at' => $comments->get(2)->updated_at->toJson(),
    //                 ]
    //             ],
    //         ]
    //     ]);
    // }

    /** @test */
    public function a_relationship_link_to_comments_returns_all_related_comments_as_resource_id_objects()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $comments = factory(Comment::class, 3)->make();
        $user->comments()->saveMany($comments);

        $this->getJson("/api/v1/users/{$user->id}/relationships/comments", [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                [
                    'id' => '1',
                    'type' => 'comments',
                ],
                [
                    'id' => '2',
                    'type' => 'comments',
                ],
                [
                    'id' => '3',
                    'type' => 'comments',
                ],
            ]
        ]);
    }

    /** @test */
    public function it_can_modify_relationships_to_comments_and_add_new_relationships()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $comments = factory(Comment::class, 10)->make();
        $user->comments()->saveMany($comments);

        $this->patchJson("/api/v1/users/{$user->id}/relationships/comments",[
            'data' => [
                [
                    'id' => '5',
                    'type' => 'comments',
                ],
                [
                    'id' => '6',
                    'type' => 'comments',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        $this->assertDatabaseHas('comments', [
            'id' => 5,
            'user_id' => $user->id,
        ])->assertDatabaseHas('comments', [
            'id' => 6,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_can_modify_relationships_to_comments_and_remove_relationships()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $comments = factory(Comment::class, 5)->make();
        $user->comments()->saveMany($comments);

        $this->patchJson("/api/v1/users/{$user->id}/relationships/comments",[
            'data' => [
                [
                    'id' => '1',
                    'type' => 'comments',
                ],
                [
                    'id' => '2',
                    'type' => 'comments',
                ],
                [
                    'id' => '5',
                    'type' => 'comments',
                ],
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        $this->assertDatabaseHas('comments', [
            'id' => 1,
            'user_id' => $user->id,
        ])->assertDatabaseHas('comments', [
            'id' => 2,
            'user_id' => $user->id,
        ])->assertDatabaseHas('comments', [
            'id' => 5,
            'user_id' => $user->id,
        ])->assertDatabaseMissing('comments', [
            'id' => 3,
            'user_id' => $user->id,
        ])->assertDatabaseMissing('comments', [
            'id' => 4,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_can_remove_all_relationships_to_comments_with_an_empty_collection()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $comments = factory(Comment::class, 3)->make();
        $user->comments()->saveMany($comments);

        $this->patchJson("/api/v1/users/{$user->id}/relationships/comments",[
            'data' => []
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(204);

        $this->assertDatabaseHas('comments', [
            'id' => 1,
            'user_id' => null,
        ])->assertDatabaseHas('comments', [
            'id' => 2,
            'user_id' => null,
        ])->assertDatabaseHas('comments', [
            'id' => 3,
            'user_id' => null,
        ]);
    }

    /** 
     * @test 
     * someone gives a comment that might not exist.
     */
    public function it_returns_a_404_not_found_when_trying_to_add_relationship_to_a_non_existing_comment()
    {
        // someone gives a comment that might not exist.
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $comments = factory(Comment::class, 3)->make();
        $user->comments()->saveMany($comments);

        $this->patchJson("/api/v1/users/{$user->id}/relationships/comments",[
            'data' => [
                [
                    'id' => '3',
                    'type' => 'comments',
                ],
                [
                    'id' => '4',
                    'type' => 'comments',
                ]
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(404)->assertJson([
            'errors' => [
                [
                    'title'   => 'Not Found Http Exception',
                    'details' => 'Resource not found',
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_validates_that_the_id_member_is_given_when_updating_a_relationship()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $comments = factory(Comment::class, 3)->make();
        $user->comments()->saveMany($comments);

        $this->patchJson("/api/v1/users/{$user->id}/relationships/comments",[
            'data' => [
                [
                    'type' => 'comments',
                ],
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title'   => 'Validation Error',
                    'details' => 'The data.0.id field is required.',
                    'source' => [
                        'pointer' => '/data/0/id',
                    ]
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_validates_that_the_id_member_is_a_string_when_updating_a_relationship()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $comments = factory(Comment::class, 3)->make();
        $user->comments()->saveMany($comments);

        $this->patchJson("/api/v1/users/{$user->id}/relationships/comments",[
            'data' => [
                [
                    'id' => 1,
                    'type' => 'comments',
                ],
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title'   => 'Validation Error',
                    'details' => 'The data.0.id must be a string.',
                    'source' => [
                        'pointer' => '/data/0/id',
                    ]
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_validates_that_the_type_member_is_given_when_updating_a_relationship()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $comments = factory(Comment::class, 3)->make();
        $user->comments()->saveMany($comments);

        $this->patchJson("/api/v1/users/{$user->id}/relationships/comments",[
            'data' => [
                [
                    'id' => '1',
                ],
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title'   => 'Validation Error',
                    'details' => 'The data.0.type field is required.',
                    'source' => [
                        'pointer' => '/data/0/type',
                    ]
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_validates_that_the_type_member_has_a_value_of_comments_when_updating_a_relationship()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $comments = factory(Comment::class, 3)->make();
        $user->comments()->saveMany($comments);

        $this->patchJson("/api/v1/users/{$user->id}/relationships/comments",[
            'data' => [
                [
                    'id' => '1',
                    'type' => 'random',
                ],
            ]
        ], [
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(422)->assertJson([
            'errors' => [
                [
                    'title'   => 'Validation Error',
                    'details' => 'The selected data.0.type is invalid.',
                    'source' => [
                        'pointer' => '/data/0/type',
                    ]
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_can_get_all_related_comments_as_resource_objects_from_related_link()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $comments = factory(Comment::class, 3)->make();
        // couple of comments to be associated with the user in order to fetch them
        $user->comments()->saveMany($comments);

        $this->getJson("/api/v1/users/{$user->id}/comments",[
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
        ])->assertStatus(200)
        ->assertJson([
            'data' => [
                [
                    "id" => '1',
                    "type" => "comments",
                    "attributes" => [
                        'message' => $comments[0]->message,
                        'created_at' => $comments[0]->created_at->toJSON(),
                        'updated_at' => $comments[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '2',
                    "type" => "comments",
                    "attributes" => [
                        'message' => $comments[1]->message,
                        'created_at' => $comments[1]->created_at->toJSON(),
                        'updated_at' => $comments[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => '3',
                    "type" => "comments",
                    "attributes" => [
                        'message' => $comments[2]->message,
                        'created_at' => $comments[2]->created_at->toJSON(),
                        'updated_at' => $comments[2]->updated_at->toJSON(),
                    ]
                ],
            ]
        ]);
    }

    /**
     * @test
     * @watch
     */
    // public function it_does_not_include_related_resource_objects_when_an_include_query_param_is_not_given() 
    // {
    //     $this->withoutExceptionHandling();
    //     $user = factory(User::class)->create();
    //     Passport::actingAs($user);

    //     $comments = factory(Comment::class, 3)->make();
    //     $user->comments()->saveMany($comments);

    //     $this->getJson("/api/v1/users/{$user->id}?include=comments", [
    //         'accept' => 'application/vnd.api+json',
    //         'content-type' => 'application/vnd.api+json',
    //     ])
    //         ->assertStatus(200)
    //         ->assertJsonMissing([
    //             'included' => [],
    //         ]);
    // }

}
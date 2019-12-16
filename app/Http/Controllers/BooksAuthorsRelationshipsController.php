<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Requests\BooksAuthorsRelationshipsRequest;
use App\Http\Requests\JSONAPIRelationshipRequest;
use App\Http\Resources\AuthorsIdentifierResource;
use App\Http\Resources\JSONAPIIdentifierResource;
use App\Services\JSONAPIService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BooksAuthorsRelationshipsController extends Controller
{

    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Book $book)
    {
        // return AuthorsIdentifierResource::collection($book->authors); // Older than using JSONAPIIdentifierResource
        // return JSONAPIIdentifierResource::collection($book->authors);

        return $this->service->fetchRelationship($book, 'authors');
    }

    // public function update(BooksAuthorsRelationshipsRequest $request, Book $book)
    public function update(JSONAPIRelationshipRequest $request, Book $book)
    {
        // Add a Gate to deny access from users and allow admins only Throw an AuthorizationException if denied
        // This is going to keep out normal users
        if (Gate::denies('admin-only')) 
        {
            throw new AuthorizationException('This action is unauthorized.');
        }

        // $ids = $request->input('data.*.id');
        // $book->authors()->sync($ids);
        // return response(null, 204);

        return $this->service->updateManyToManyRelationships($book, 'authors', $request->input('data.*.id'));
    }
}

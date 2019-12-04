<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Resources\AuthorsCollection;
use App\Services\JSONAPIService;
use Illuminate\Http\Request;

class BooksAuthorsRelatedController extends Controller
{

    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Book $book)
    {
        // We already have a collection for our authors.
        // So we can reuse this and send in a collection of the authors that are related to our book
        // return new AuthorsCollection($book->authors);

        return $this->service->fetchRelated($book, 'authors');
    }
}

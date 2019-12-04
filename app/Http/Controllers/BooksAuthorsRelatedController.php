<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Resources\AuthorsCollection;
use Illuminate\Http\Request;

class BooksAuthorsRelatedController extends Controller
{
    public function index(Book $book)
    {
        // We already have a collection for our authors.
        // So we can reuse this and send in a collection of the authors that are related to our book
        return new AuthorsCollection($book->authors);
    }
}

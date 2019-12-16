<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Requests\CreateBookRequest;
use App\Http\Requests\JSONAPIRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BooksCollection;
use App\Http\Resources\BooksResource;
use App\Http\Resources\JSONAPICollection;
use App\Http\Resources\JSONAPIResource;
use App\Services\JSONAPIService;
use Illuminate\Http\Request;

class BooksController extends Controller
{

    private $service;
    
    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
        // Allow the controller to use our Policy "BookPolicy"
        // This helper method is automatically adding a middleware in front of all of our methods in our controller
        $this->authorizeResource(Book::class, 'book');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $books = Book::all();
        // // return new BooksCollection($books);
        // return new JSONAPICollection($books);

        return $this->service->fetchResources(Book::class, 'books');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // public function store(CreateBookRequest $request)
    public function store(JSONAPIRequest $request)
    {
        // we need to add the CreateBookRequest to our store method so that we are actually validating against the rules.

        /**
         * We get the data from the request using the input method, 
         * and we leverage the create static method on our model to do the entire creation of our book.
         * We then use our BooksResource to return the book as a resource object adhering to the JSON:API specification.
         */
        // $book = Book::create([
        //     'title' => $request->input('data.attributes.title'),
        //     'description' => $request->input('data.attributes.description'),
        //     'publication_year' => $request->input('data.attributes.publication_year'),
        // ]);

        // // return (new BooksResource($book))
        // return (new JSONAPIResource($book))
        //     ->response()
        //     ->header('Location', route('books.show', [
        //         'book' => $book,
        //     ]));

        return $this->service->createResource(Book::class, $request->input('data.attributes'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function show(Book $book)
    // public function show($book)
    {
        // return $book;
        // return new BooksResource($book);
        return new JSONAPIResource($book);

        // return $this->service->fetchResource(Book::class, $book, 'books');
        
        // Requires the Spatie Query Builder package which I didnot import due to compatibility issues with Laravel 6
        // $query = QueryBuilder::for(Book::where('id', $book))
        //     ->allowedIncludes('authors')
        //     ->firstOrFail();
        // return new BooksResource($query);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Book $book)
    // public function update(UpdateBookRequest $request, Book $book)
    public function update(JSONAPIRequest $request, Book $book)
    {
        // When we use the UpdateBookRequest class, it gives us the ability to validate the request against our set rules under App\Http\Requests\UpdateBookRequest.php
        // Having it as (Request $request ..) does not add validation to our API requests.
        // $book->update($request->input('data.attributes'));
        // // return new BooksResource($book);
        // return new JSONAPIResource($book);

        // return $this->service->updateResource($book, $request->input('data.attributes'));
        return $this->service->updateResource($book, $request->input('data.attributes'), $request->input('data.relationships'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        // $book->delete();
        // return response(null, 204);

        return $this->service->deleteResource($book);
    }
}

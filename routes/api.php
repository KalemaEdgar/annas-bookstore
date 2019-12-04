<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
// Added by Kalema Edgar for learning
// The route below is using the auth:api middleware
// It tells us that only authenticated users can make requests to this route since it’s protected by our authentication middleware.
*/
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Modified the above route to add versioning of the API
// Since our route is protected by the auth:api middleware, you cant access it without the Bearer token
Route::middleware('auth:api')->prefix('v1')->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Authors
    Route::apiResource('authors', 'AuthorsController'); // Single route that includes all the API required routes. Use php artisan route:list to see all the routes generated for authors
    // Route::get('/authors', 'AuthorsController@index');
    // Route::get('/authors/{author}', 'AuthorsController@show');

    // Books
    Route::apiResource('books', 'BooksController');

    // ------------------------------------
    // Books Authors Relationship routes
    // ------------------------------------
    Route::get('books/{book}/relationships/authors', 'BooksAuthorsRelationshipsController@index')->name('books.relationships.authors');

    Route::patch('books/{book}/relationships/authors', 'BooksAuthorsRelationshipsController@update')->name('books.relationships.authors');

    // Route for the related link - Get a collection of authors related to a certain book
    Route::get('books/{book}/authors', 'BooksAuthorsRelatedController@index')->name('books.authors');

    // Route::get('books/{book}/authors', function () {
    //     return true;
    // })->name('books.authors');
    
});

// This route is protected using the Client Credentials Grant (OAuth2 grant).
// Uses the middleware client which uses tokens and user details to register the client to the server
// Route::get('/test', function(Request $request) {
//     return 'authenticated';
// })->middleware('client');

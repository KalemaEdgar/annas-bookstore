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

    // Users
    Route::apiResource('users', 'UsersController');

    Route::get('users/{user}/relationships/comments', 'UsersCommentsRelationshipsController@index')->name('users.relationships.comments');
    Route::patch('users/{user}/relationships/comments', 'UsersCommentsRelationshipsController@update')->name('users.relationships.comments');
    Route::get('users/{user}/comments', 'UsersCommentsRelatedController@index')->name('users.comments');

    Route::get('/user/current', function (Request $request) {
        return $request->user();
    });

    // Route::get('/users/{user_id}', 'UsersController@show');

    // Authors
    Route::apiResource('authors', 'AuthorsController'); // Single route that includes all the API required routes. Use php artisan route:list to see all the routes generated for authors
    // Route::get('/authors', 'AuthorsController@index');
    // Route::get('/authors/{author}', 'AuthorsController@show');

    // Books
    Route::apiResource('books', 'BooksController');

    // ------------------------------------
    // Books Authors Relationship routes - To be able to pick the authors for a certain book
    // ------------------------------------
    // Make a GET request to the relationship and get the resource identifier objects of books related to the author
    Route::get('books/{book}/relationships/authors', 'BooksAuthorsRelationshipsController@index')->name('books.relationships.authors');

    // We want to be able to make a PATCH request to the relationship to update it without adding or deleting resources themselves
    Route::patch('books/{book}/relationships/authors', 'BooksAuthorsRelationshipsController@update')->name('books.relationships.authors');

    // Route for the related link - Get a collection of authors related to a certain book
    // We want to be able to get the related resource objects
    Route::get('books/{book}/authors', 'BooksAuthorsRelatedController@index')->name('books.authors');

    Route::get('books/{book}/relationships/comments', 'BooksCommentsRelationshipsController@index')->name('books.relationships.comments');
    Route::patch('books/{book}/relationships/comments', 'BooksCommentsRelationshipsController@update')->name('books.relationships.comments');
    Route::get('books/{book}/comments', 'BooksCommentsRelatedController@index')->name('books.comments');

    // Route::get('books/{book}/authors', function () {
    //     return true;
    // })->name('books.authors');

    // ------------------------------------
    // Authors Books Relationship routes - To be able to pick the books for a certain author
    // ------------------------------------
    Route::get('authors/{author}/relationships/books', 'AuthorsBooksRelationshipsController@index')->name('authors.relationships.books');
    
    Route::patch('authors/{author}/relationships/books', 'AuthorsBooksRelationshipsController@update')->name('authors.relationships.books');
    
    Route::get('authors/{author}/books', 'AuthorsBooksRelatedController@index')->name('authors.books');

    // Comments
    Route::apiResource('comments', 'CommentsController');
    Route::get('comments/{comment}/relationships/users', 'CommentsUsersRelationshipsController@index')->name('comments.relationships.users');
    Route::patch('comments/{comment}/relationships/users', 'CommentsUsersRelationshipsController@update')->name('comments.relationships.users');
    Route::get('comments/{comment}/users', 'CommentsUsersRelatedController@index')->name('comments.users');

    Route::get('comments/{comment}/relationships/books', 'CommentsBooksRelationshipsController@index')->name('comments.relationships.books');
    Route::patch('comments/{comment}/relationships/books', 'CommentsBooksRelationshipsController@update')->name('comments.relationships.books');
    Route::get('comments/{comment}/books', 'CommentsBooksRelatedController@index')->name('comments.books');
    
});

// This route is protected using the Client Credentials Grant (OAuth2 grant).
// Uses the middleware client which uses tokens and user details to register the client to the server
// Route::get('/test', function(Request $request) {
//     return 'authenticated';
// })->middleware('client');

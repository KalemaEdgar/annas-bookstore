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
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

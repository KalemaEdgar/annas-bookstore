<?php

namespace App\Http\Controllers;

use App\Author;
use App\Http\Requests\CreateAuthorRequest;
use App\Http\Requests\JSONAPIRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Http\Resources\AuthorsResource;
use App\Http\Resources\JSONAPICollection;
use App\Http\Resources\JSONAPIResource;
use App\Services\JSONAPIService;
use Illuminate\Http\Request;

class AuthorsController extends Controller
{

    private $service;

    // Inject our services file and initialize it to a service property
    // Laravel will take care of injecting the service into our controller whenever a request comes in.
    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     * For APIs, this is used to get a list of all authors / elements using route /api/v1/authors (GET method)
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $authors = Author::all();
        // // return AuthorsResource::collection($authors); Older than using JSONAPICollection
        // return new JSONAPICollection($authors);

        return $this->service->fetchResources(Author::class);
    }

    /**
     * Store a newly created resource in storage.
     * For APIs, this is used to add / create a new entity like an author using route /api/v1/authors (POST method)
     * We are using the CreateAuthorRequest form request to add validation on our requests
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // public function store(CreateAuthorRequest $request) // Older
    public function store(JSONAPIRequest $request)
    {
        // It’s our responsibility to take the data from a resource object and create a new author from these
        // so let’s pretend that we got a correct resource object and create the model
        // CreateAuthorRequest class offers validation to this method. Ensures we get the required data on each request
        // $author = Author::create([
        //     'name' => $request->input('data.attributes.name'),
        // ]);

        // return (new AuthorsResource($author)) // Older than using the JSONAPIResource
        // return (new JSONAPIResource($author))
        //     ->response()
        //     ->header('Location', route('authors.show', ['author' => $author]));

        return $this->service->createResource(Author::class, $request->input('data.attributes'));
    }

    /**
     * Display the specified resource.
     * For APIs, this is to display details for a single resource using route /api/v1/authors/1 (GET method)
     * @param  \App\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function show(Author $author)
    {

        // return new AuthorsResource($author);
        // return new JSONAPIResource($author);
        return $this->service->fetchResource($author);
        
        // return $author;

        // return response()->json([
        //     'data' => [
        //         'id' => $author->id,
        //         'type' => 'authors',
        //         'attributes' => [
        //             'name' => $author->name,
        //             'created_at' => $author->created_at,
        //             'updated_at' => $author->updated_at,
        //         ]
        //     ]
        // ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Author  $author
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Author $author)
    // public function update(UpdateAuthorRequest $request, Author $author)
    public function update(JSONAPIRequest $request, Author $author)
    {
        // Take this model and update it according to the attributes in the resource object.
        // $author->update($request->input('data.attributes'));
        // // return new AuthorsResource($author); // Older than using JSONAPIResource
        // return new JSONAPIResource($author);

        return $this->service->updateResource($author, $request->input('data.attributes'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function destroy(Author $author)
    {
        // $author->delete();
        // // Respond with no data and a status code 204 No Content
        // return response(null, 204);

        return $this->service->deleteResource($author);
    }
}

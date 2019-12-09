<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Requests\JSONAPIRequest;
use App\Services\JSONAPIService;
use Illuminate\Http\Request;

class CommentsController extends Controller
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
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->service->fetchResources(Comment::class);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JSONAPIRequest $request)
    {
        // return $this->service->createResource(Comment::class, $request->input('data.attributes'));
        return $this->service->createResource(Comment::class, $request->input('data.attributes'), $request->input('data.relationships'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        return $this->service->fetchResource($comment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(JSONAPIRequest $request, Comment $comment)
    {
        // return $this->service->updateResource($comment, $request->input('data.attributes'));
        return $this->service->updateResource($comment, $request->input('data.attributes'), $request->input('data.relationships'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        return $this->service->deleteResource($comment);
    }
}

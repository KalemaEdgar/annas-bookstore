<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureCorrectAPIHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class EnsureCorrectAPIHeadersTest extends TestCase
{

    /**
     * We can reject the request with a response "406 Not Acceptable" status code, if the Accept header is not correct (Accept: application/vnd.api+json), as stated in the JSON:API specification.
     * We will also ensure that we respond with a 415 Unsupported Media Type if we don’t receive the correct Content-Type header (Content-Type: application/vnd.api+json)
     * Ensure that we send all our responses from the server with the correct Content-Type header.
     * We are using a middleware to offer this functionality of validating the headers
     * 
     * @test
     */
    public function it_aborts_request_if_accept_header_does_not_adhere_to_json_api_spec()
    {
        // Create a new request
        // Create an instance of our middleware. first argument of the middleware is the request and second is an anonymous function.
        // The anonymous function is used to test that the middleware is not forwarding the request to the next middleware, and if it does, we will force the test to fail.

        $request = Request::create('/test', 'GET');
        $middleware = new EnsureCorrectAPIHeaders;

        /** @var Response $response */
        $response = $middleware->handle($request, function($request) {
            $this->fail('Did not abort request because of invalid Accept header');
        });

        $this->assertEquals(406, $response->status());
    }

    /** @test */
    public function it_accepts_request_if_accept_header_adheres_to_json_api_spec()
    {
        // To make sure we are not rejecting everyone that makes requests to our API,
        // let’s test that requests, which contain the accept header, get through the middleware.

        $request = Request::create('/test', 'GET');
        // Set the header on the request since we need it to exist and with the right value
        $request->headers->set('accept', 'application/vnd.api+json');

        $middleware = new EnsureCorrectAPIHeaders;

        // This time we want the middleware to forward the request to the closure
        $response = $middleware->handle($request, function($request) {
            return new Response();
        });

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function it_aborts_post_request_if_content_type_header_does_not_adhere_to_json_api_spec()
    {
        $request = Request::create('/test', 'POST');
        $request->headers->set('accept', 'application/vnd.api+json');

        $middleware = new EnsureCorrectAPIHeaders;
        
        $response = $middleware->handle($request, function($request) {
            $this->fail('Did not abort request because of invalid Content-Type header');
        });

        $this->assertEquals(415, $response->status());
    }

    /** @test */
    public function it_aborts_patch_request_if_content_type_header_does_not_adhere_to_json_api_spec()
    {
        $request = Request::create('/test', 'PATCH');
        $request->headers->set('accept', 'application/vnd.api+json');

        $middleware = new EnsureCorrectAPIHeaders;
        
        $response = $middleware->handle($request, function($request) {
            $this->fail('Did not abort request because of invalid Content-Type header');
        });

        $this->assertEquals(415, $response->status());
    }

    /** @test */
    public function it_accepts_post_request_if_content_type_header_adheres_to_json_api_spec()
    {
        $request = Request::create('/test', 'POST');
        $request->headers->set('accept', 'application/vnd.api+json');
        $request->headers->set('content-type', 'application/vnd.api+json');

        $middleware = new EnsureCorrectAPIHeaders;
        
        $response = $middleware->handle($request, function($request) {
            return new Response();
        });

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function it_accepts_patch_request_if_content_type_header_adheres_to_json_api_spec()
    {
        $request = Request::create('/test', 'PATCH');
        $request->headers->set('accept', 'application/vnd.api+json');
        $request->headers->set('content-type', 'application/vnd.api+json');

        $middleware = new EnsureCorrectAPIHeaders;
        
        $response = $middleware->handle($request, function($request) {
            return new Response();
        });

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function it_ensures_that_a_content_type_header_adhering_to_json_api_spec_is_on_response() 
    {
        // Initiate a request
        $request = Request::create('/test', 'GET');
        // Set the correct request headers
        $request->headers->set('accept', 'application/vnd.api+json');
        $request->headers->set('content-type', 'application/vnd.api+json');

        // Instatiate the middleware
        $middleware = new EnsureCorrectAPIHeaders;

        $response = $middleware->handle($request, function($request) {
            return new Response();
        });

        $this->assertEquals(200, $response->status());
        $this->assertEquals('application/vnd.api+json', $response->headers->get('content-type'));
    }

    /** 
     * Test to ensure that the correct Content-Type header is set even for failed requests
     * In this scenario, the requests headers are not set simulating a failure
     * @test 
     */
    public function when_aborting_for_a_missing_accept_header_the_correct_content_type_header_is_set()
    {
        $request = Request::create('/test', 'GET');
        $middleware = new EnsureCorrectAPIHeaders;
        
        $response = $middleware->handle($request, function($request){
            return new Response();
        });

        $this->assertEquals($response->status(), 406);
        $this->assertEquals('application/vnd.api+json', $response->headers->get('content-type'));
    }

}
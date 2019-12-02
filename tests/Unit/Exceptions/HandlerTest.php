<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    /**
     * Test that a General Exception is being handled appropriately by our app 
     * @test */
    public function it_converts_an_exception_into_a_json_api_spec_error_response() 
    {
        $handler = app(Handler::class);

        $request = Request::create('/test', 'GET');
        $request->headers->set('accept', 'application/vnd.api+json');

        $exception = new \Exception('Test exception');

        $response = $handler->render($request, $exception);

        TestResponse::fromBaseResponse($response)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Exception',
                        'details' => 'Test exception',
                    ]
                ]
            ])
            ->assertStatus(500);
    }

    /**
     * Test that an HTTP Exception is being handled appropriately by our app 
     * @test */
    public function it_converts_an_http_exception_into_a_json_api_spec_error_response() 
    {
        $handler = app(Handler::class);

        $request = Request::create('/test', 'GET');
        $request->headers->set('accept', 'application/vnd.api+json');

        $exception = new HttpException(404, 'Not found');

        $response = $handler->render($request, $exception);

        TestResponse::fromBaseResponse($response)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Http Exception',
                        'details' => 'Not found',
                    ]
                ]
            ])
            ->assertStatus(404);
    }

    /**
     * Test that an Authentication Exception is being handled appropriately by our app 
     * @test */
    public function it_converts_an_unauthenticated_exception_into_a_json_api_spec_error_response() 
    {
        $handler = app(Handler::class);

        $request = Request::create('/test', 'GET');
        $request->headers->set('accept', 'application/vnd.api+json');

        $exception = new AuthenticationException();

        $response = $handler->render($request, $exception);

        TestResponse::fromBaseResponse($response)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'Unauthenticated',
                        'details' => 'You are not authenticated',
                    ]
                ]
            ]);
    }

}
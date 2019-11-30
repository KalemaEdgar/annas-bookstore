<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class EnsureCorrectAPIHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if the Accept header is present and it has the value: application/vnd.api+json
        // If it doesn’t, we will return a new response with a 406 Not Acceptable status code
        if ($request->headers->get('accept') != 'application/vnd.api+json') {
            // return new Response('', 406); // Initial response without the content-type header
            return $this->addCorrectContentType(new Response('', 406)); // Adds the Content-Type header to adhere to the JSON:API spec
        }

        if ($request->isMethod('POST') || $request->isMethod('PATCH')) {
            if ($request->headers->get('content-type') != 'application/vnd.api+json') {
                // return new Response('', 415); // Initial response without the content-type header
                return $this->addCorrectContentType(new Response('', 415)); // Adds the Content-Type header to adhere to the JSON:API spec
            }
        }

        return $this->addCorrectContentType($next($request));
        
    }

    private function addCorrectContentType(BaseResponse $response)
    {
        $response->headers->set('content-type', 'application/vnd.api+json');
        return $response;
    }

}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

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
            return new Response('', 406);
        }

        if ($request->isMethod('POST') || $request->isMethod('PATCH')) {
            if ($request->headers->get('content-type') != 'application/vnd.api+json') {
                return new Response('', 415);
            }
        }

        return $next($request);
    }

}

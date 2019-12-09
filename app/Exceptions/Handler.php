<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     * for tracking and logging exceptions so you can get a better understanding of what went wrong.
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     * For presenting the exception to the user.
     * It depends on the debug state of your application as well as the Accept header of the request.
     * If your application has debugging enabled, you will get a nice error page where you can dig down into the stack trace of the exception.
     * If your Accept header is set to application/json or alike, you will receive a detailed error response with the entire stack trace in JSON.
     * If your application has debugging disabled, you will see an error page or a short JSON object with a short message.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof QueryException || $exception instanceof ModelNotFoundException)
        {
            $exception = new NotFoundHttpException('Resource not found');
        }

        return parent::render($request, $exception);
    }

    /**
     * Overriding the invalid-Json method, to allow us have custom error messages from Laravel for our API
     * This is being utilised
     *
     * @param [type] $request
     * @param ValidationException $exception
     * @return void
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        // Here, we instantiate a new Collection and give it the validator’s array of errors.
        // Through the map method, we go through each entry in the array and map it into the desired error object that adheres to the JSON:API specification.
        // Lastly, we call the values method on the collection, which will reset the keys to consecutive integers.
        $errors = ( new Collection($exception->validator->errors()) )
            ->map(function ($error, $key) {
                return [
                    'title'   => 'Validation Error',
                    'details' => $error[0],
                    'source'  => [
                        'pointer' => '/' . str_replace('.', '/', $key),
                    ]
                ];
            })
            ->values();

        return response()->json([
            'errors' => $errors
        ], $exception->status);
    }

    protected function prepareJsonResponse($request, Exception $e)
    {
        return response()->json([
            'errors' => [
                [
                    'title' => Str::title(Str::snake(class_basename($e), ' ')),
                    'details' => $e->getMessage(),
                ]
            ]
        ], $this->isHttpException($e) ? $e->getStatusCode() : 500);
    }

    /**
     * This is to override the method handling the Laravel Authetication exceptions
     * We are overriding since we want the app to have custom messages properly written instead of the default messages from Laravel
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'errors' => [
                    [
                        'title' => 'Unauthenticated',
                        'details' => 'You are not authenticated',
                    ]
                ]
            ], 403);
        }
        
        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }

}

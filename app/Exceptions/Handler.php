<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;

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
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
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

}

<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use App\Exceptions\UnauthorizedException;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        ValidatorException::class
    ];


    protected array $exceptionMap = [
        ModelNotFoundException::class => [
            'code' => 404,
            'message' => 'Could not find what you were looking for.',
            'adaptMessage' => false,
        ],
        ItemNotFoundException::class => [
            'code' => 404,
            'message' => 'Could not find what you were looking for.',
            'adaptMessage' => false,
        ],        
        NotFoundHttpException::class => [
            'code' => 404,
            'message' => 'Could not find what you were looking for.',
            'adaptMessage' => false,
        ],
        
        MethodNotAllowedHttpException::class => [
            'code' => 405,
            'message' => 'This method is not allowed for this endpoint.',
            'adaptMessage' => false,
        ],
        
        ValidationException::class => [
            'code' => 422,
            'message' => 'Some data failed validation in the request',
            'adaptMessage' => false,
        ],
        ValidatorException::class => [
            'code' => 422,
            'message' => 'Some data failed validation in the request',
            'adaptMessage' => false,
        ],
        \InvalidArgumentException::class => [
            'code' => 400,
            'message' => 'You provided some invalid input value',
            'adaptMessage' => true,
        ],
        UnauthorizedException::class => [
            'code' => 401,
            'message' => 'Unauthorized access.',
            'adaptMessage' => false,
        ],
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $response = $this->formatException($exception);
    
        return response()->json($response, $response['status'] ?? 500);
    }

    protected function formatException(\Throwable $exception): array
    {
        $exceptionClass = get_class($exception);
        $environment = config('app.env');
        $definition = $this->exceptionMap[$exceptionClass] ?? [
            'code' => 500,
            'message' => ($environment=='local') ? ($exception->getMessage() ?? 'Something went wrong while processing your request') : 'Something went wrong while processing your request',
            'adaptMessage' => false,
        ];
    
        if (empty($definition['adaptMessage']) === false) {        
            $definition['message'] = ($environment=='local') ? ($exception->getMessage() ?? $definition['message']) : 'Something went wrong while processing your request';        
        }
        
        $response = [
            'status' => $definition['code'] ?? 500,
            'message' => $definition['message'],
            'success' => false,
            'data' => [],
            'errors' => method_exists($exception, 'getMessageBag') == true ? $exception->getMessageBag()->toArray() : []
        ];
        Log::error($exception);
        return $response;
    }
}
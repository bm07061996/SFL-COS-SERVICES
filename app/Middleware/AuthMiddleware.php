<?php

namespace App\Middleware;

use App\Utils\RestServiceTrait;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    use RestServiceTrait;

    public function handle($request, Closure $next)
    {
        $key = config('jwt.secret');
        $authorization = $request->header('Authorization');
        if (empty($authorization) === true) {
            throw new Exception('Authorization header not found');
        }
        $decodedToken = JWT::decode($authorization, new Key($key, 'HS256'));
        if (empty($decodedToken->data->transKey) === true) {
            throw new Exception('Transaction key not found');
        }
        $request->attributes->add([
            'transKey' => $decodedToken->data->transKey
        ]);
        return $next($request);
    }
}

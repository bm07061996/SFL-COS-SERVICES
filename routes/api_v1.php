<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', ['as' => 'api.version', 'uses' => 'ApiService@version']);
$router->post('authentication', 'AuthService@login');

$router->group(['middleware' => ['auth','postLoginValidator']], function () use ($router): void {
    $router->post('postLogin', 'PostLoginService@process');
});



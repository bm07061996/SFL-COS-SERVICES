<?php
namespace App\Providers;

use Exception;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $request = app('request');
        $apiVersion = $request->header('api-version') ?? 'v1';
        $apiVersion = app()->runningInConsole() ? 'v1' : $apiVersion;
        if(empty($apiVersion) === true){
            throw new Exception("API version is not passed in header.");
        }

        switch ($apiVersion) {
            case 'v1':
                app('router')->group(['namespace' => 'App\Services\V1', 'prefix' => 'api/v1'], function ($router) {
                    require __DIR__.'/../../routes/api_v1.php';
                });
                break;
            
            default:
                throw new Exception("API version is not available.");
                break;
        }
    }
}

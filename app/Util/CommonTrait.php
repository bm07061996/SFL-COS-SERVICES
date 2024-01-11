<?php
namespace App\Util;

use DB;
use Log;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use App\Libraries\StorageManager;
use Illuminate\Support\Facades\Redis;

trait CommonTrait
{
    protected static $client;
    protected static $redisConnection;

    private function guthGeneration($string, $split = 3)
    {
        $length =   strlen($string);
        if($length <= $split){
            $arrGuthValues[]    =   $string;
            return $arrGuthValues;
        }

        $length =   $length - $split;   
        for($i = 0; $i <= $length; $i++){
            $substr =   substr($string, $i, $split);
            $arrGuthValues[]    =   $substr;            
        }
        return $arrGuthValues;
    }

    public function guth($arrInputValues, $arrSystemValues, $params)
    {
        $ratio = 80;
        $split = 3;
        if(empty($params) === false){
            $paramData = explode(',', $params);
            if(count($paramData) > 1){
                $split = $paramData[0];
                $ratio = $paramData[1];
            }
        }

        $arrInputValues = strtolower($arrInputValues);
        $arrSystemValues = strtolower($arrSystemValues);
        $arrInputGuthValues = $this->guthGeneration($arrInputValues, $split);
        $arrSystemGuthValues = $this->guthGeneration($arrSystemValues, $split);
        $matchCount = 0;
        foreach($arrInputGuthValues as $sourceByte){
            foreach($arrSystemGuthValues as $targetByte){
                if($sourceByte == $targetByte){
                    $matchCount++;
                    break;
                }
            }           
        }

        $matchPercent = ($matchCount * 100) / count($arrInputGuthValues);
        \Log::info('Guth-percentage : '.$matchPercent);
        return ($matchPercent >= $ratio) ? true : false;
        
    }

    public function callGetRequestApi($url)
    {
        $client = app()->make(Client::class);
        try {
            $response = $client->request('GET', $url);
        } 
        catch (\Exception $e) {
            $error = sprintf('%s:%s File: %s , Line: %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
            \Log::info('Api Get Call Error : '.$error);
            $this->exceptionEmailTrigger('API Get Call Error ', $error.' URL: '.$url);
            return false;
        }
        
        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody()->getContents(), true);
        }

        return false;
    }

    public function callPostRequestApi($url, $data, $returnOriginalResponse = false) {
        $client = app()->make(Client::class);

        try {
            $response = $client->request('POST', $url, [
                'form_params' => $data,
                'timeout' => 120
            ]);
        }
        catch (\Exception $e) {
            $error = sprintf('%s:%s File: %s , Line: %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
            \Log::info('Api Post Call Error : '.$error);
            // $this->exceptionEmailTrigger('API Post Call Error ', $error.' URL: '.$url);
            return false;
        }
        
        if ($response->getStatusCode() === 200) {
            return $returnOriginalResponse === false ? json_decode($response->getBody()->getContents(), true) : $response;
        }

        return false;
    }

    public function callPostRequestWithHeaders($url, $data, $headers)
    {
        try{
            $client = app()->make(Client::class);
            $response = $client->request('POST', $url, [
                'form_params' => $data,
                'headers'   => $headers,
                'timeout' => 60
            ]);
            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }
        }
        catch(ClientException $e) {
            \Log::info('API Exception: '. $e->getMessage());
        }
        catch (ServerException $e) {
            \Log::info('API Server Exception: '. $e->getMessage());
        }
        catch(\Exception $e){
            $error = sprintf('%s:%s File: %s , Line: %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
            \Log::info('Api Post JSON Call Error : '.$error);
            // $this->exceptionEmailTrigger('Api Call JSON Post ', $error.' URL: '.$url);
        }        

        return false;
    }

    public function callJsonPostRequest($url, $data)
    {
        try{
            $client = app()->make(Client::class);
            $response = $client->request('POST', $url, [
                'json' => $data,
                'headers'   => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'timeout' => 60
            ]);
            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }
        }
        catch(ClientException $e) {
            \Log::info('API Exception: '. $e->getMessage());
        }
        catch (ServerException $e) {
            \Log::info('API Server Exception: '. $e->getMessage());
        }
        catch(\Exception $e){
            $error = sprintf('%s:%s File: %s , Line: %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
            \Log::info('Api Post JSON Call Error : '.$error);
            $this->exceptionEmailTrigger('Api Call JSON Post ', $error.' URL: '.$url);
        }        

        return false;
    }

    public function getRedisConnection()
    {
        return $this->getRedisConnectionInstance();
    }

    public function getRedisConnectionInstance() 
    {
        if (is_null(static::$redisConnection)) {
            \Log::info("New Redis connection");
            static::$redisConnection = Redis::connection();
        } else {
            \Log::info("Old Redis Connection");
        }

        return static::$redisConnection;
    }

    public function getRedisValue($key, $responseInArray = false)
    {
        $data = $this->getRedisConnection()->get($key);
        return empty($responseInArray) === false ? json_decode($data, true) : $data;
    }

    public function getStorageInstance()
    {
        return app()->make(StorageManager::class);
    }

    public function getAge($dob)
    {
        try{
            $d1 = Carbon::parse($dob);
            $d2 = Carbon::now();
            $age = $d1->diff($d2)->format('%y');
            
            return $age;
        }
        catch(\Exception $e){
            return 0;
        }
        
        return 0;
    }

    public function getUserAuthorization($url, $userId)
    {
        $client = app()->make(Client::class);
        try {
              $request = $client->request('POST', $url, [
                'json' => [ 'userId' => $userId ],
                'timeout' => 60
              ]);

            if($request->getStatusCode()==200) {
                $response = $request->getHeaders();
                $responseToken = head(array_pull($response, 'Authorization')); 
                return $responseToken;
            }
        } catch (\Exception $e) {
            \Log::error("Authorization Api- ". $e->getMessage());
            return false;
        }
        return false;        
    }

    public function getGuzzleClientObj()
    {
        if (is_null(static::$client)) {
            static::$client = app()->make(Client::class);
            \Log::info("Inside new Client");
            return static::$client;
        } else {
            \Log::info("Old object of Client");
            return static::$client;
        }
    }

    public function indianINRFormat($value)
    {
        return preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", ceil($value));
    }

}

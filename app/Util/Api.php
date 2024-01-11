<?php

namespace App\Util;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Redis;

trait Api
{
	public $client;

	public $apiResponse;

	public function getGuzzleClient()
	{
		if (is_null($this->client)) {
			$this->client = app()->make(Client::class);
		}

		return $this->client;
	}

	public function apiCall($url, $options = [], $method = "GET")
	{
		try {
			$options['timeout'] = empty($options['timeout']) === false ? $options['timeout'] : 60;

			$response = $this->getGuzzleClient()->request($method, $url, $options);

			$this->statusCode = $response->getStatusCode();
			
			if ($response->getStatusCode() === 200) {

				$this->apiResponse = $response->getBody()->getContents();

				return $this;
			}
			
		} catch(\ClientException $e) {
			Log::error($e->getMessage());
		}
		
		return null;
	}

	public function toArray()
	{
		return json_decode($this->apiResponse, true);
	}

    public function curl($postData,$url,$headerContent) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = $headerContent;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);

        curl_close ($ch);
        $output=json_decode($server_output,true);
        return $output;
    }

    public function encryptAuthKey(){

        try{
        	$authKeyDateFmt = date("Y-m-d\TH:i:s\Z");
            $password = config('app.fd_authkey_pwd');
            $plaintext = config('app.fd_authkey_txt').$authKeyDateFmt;
            $method = config('app.fd_authkey_cipher');
            $iv = config('app.fd_authkey_iv');
	        $cipher = openssl_encrypt($plaintext, $method, $password, $options=OPENSSL_RAW_DATA,$iv);
	        $encAuthKey = base64_encode($cipher);
	        return $encAuthKey;
        }catch(\Exception $e){
            \log::info('Exception'.$e->getMessage());    
        }
    }

    public function fdDecryptAES($data)
    {
        try{
            $key = config('app.cp_decrypted_key');
            $iv = config('app.cp_decrypted_iv');
            $cipher = config('app.cipher_aes');
            return openssl_decrypt($data, $cipher, $key, $options=0, $iv);           
        }catch(\Exception $e){   
            \log::info('Exception'.$e->getMessage());    
        } 
    }

    public function fdEncryptAES($data)
    {
        try{
            $key = config('app.cp_encrypted_key');
            $iv = config('app.cp_encrypted_iv');
            $cipher = config('app.cipher_aes');
            return openssl_encrypt($data, $cipher, $key, $options=0, $iv);
        }catch(\Exception $e){
            \log::info('Exception'.$e->getMessage());    
        }
    }

    public function nameMatching($match , $against, $percent, $split = 3)
    {
        $match = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($match));
        $against = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($against));
        $matchGuthValue = $this->createGuthValues($match, $split);
        $againstGuthValue = $this->createGuthValues($against, $split);
        
        $matches = $this->applyGuthLogic($matchGuthValue, $againstGuthValue);
        
        return [
            'isMatched' => ceil($matches) >= $percent ? true : false,
            'matchPercentage' => ceil($matches)
        ];
    }

    public function createGuthValues($string, $split = 3)
    {
        $guthValues = [];
        $string = strtolower($string);
        $length =   strlen($string);
        if($length <= 3) {
            $guthValues[] = $string;
        } else {
            $length = $length - $split;

            for($i = 0; $i <= $length; $i++) {
                $substr =  substr($string, $i, 3);
                $guthValues[] = $substr;           
            }
        }

        return $guthValues;
    }

    public function applyGuthLogic($matchGuth, $againstGuth)
    {
        $matchCount = 0;
        $totalCount = (count($matchGuth) > count($againstGuth)) ? $againstGuth : $matchGuth;     
        foreach($matchGuth as $value) {
            if (in_array($value, $againstGuth) === true) {          
                $matchCount++;
            }            
        }
        $matchPercent = ($matchCount * 100) / count($totalCount);

        return $matchPercent;
    }

    public function rtoCurl($postData,$url,$headerContent) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = $headerContent;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);
        //print_r($server_output);
        curl_close ($ch);
        
        return $server_output;
    }

    public function rtoEncryptAES($data)
    {
        try{
            $key = config('app.encrypted_rto_key');
            $iv = config('app.encrypted_rto_iv');
            $cipher = config('app.cipher_aes');
            return openssl_encrypt($data, $cipher, $key, $options=0, $iv);
        }catch(\Exception $e){
            \log::info('Exception'.$e->getMessage());    
        }
    }

    public function rtoDecryptAES($data)
    {
        try{
            $key = config('app.decrypted_rto_key');
            $iv = config('app.decrypted_rto_iv');
            $cipher = config('app.cipher_aes');
            return openssl_decrypt($data, $cipher, $key, $options=0, $iv);           
        }catch(\Exception $e){   
            \log::info('Exception'.$e->getMessage());    
        } 
    }

    public function mobileNoDecryptAES($data)
    {
        try{
            $key = config('app.encrypted_key');
            $iv = config('app.encrypted_rto_iv');
            $cipher = config('app.cipher_aes');
            return openssl_decrypt($data, $cipher, $key, $options=0, $iv);           
        }catch(\Exception $e){   
            \log::info('Exception'.$e->getMessage());    
        } 
    }

    public function getLeadApiAuth() {
        $token="";
        try {
            $url = config('api.sitLeadApiUrl')."/authenticate";
            $response = $this->apiCall($url);
            Log::info('auth1'.json_encode($response));
            if(!empty($response)) {
                $responseData =  empty($response->apiResponse) === false ? json_decode($response->apiResponse,true) : '';
                Log::info('responseData :'.json_encode($responseData['status']));
                if(!empty($responseData) && !empty($responseData['status']) && $responseData['status'] == 'success' && !empty($responseData['data']) && !empty($responseData['data']['token'])) {
                    $token = $responseData['data']['token'];
                }
            }
        } catch(\Throwable | \Exception | ClientException $throwable) {
            Log::info('GetLeadApiAuthException: '.$throwable->getMessage());
        }
        Log::info('GetLeadApiAuthToken:'.$token);
        $result['token'] = $token;
        return $result;
    }

    public function apiDecryptAES($data) {
        try{
            $key = config('app.api_encrypt_key');
            $iv = config('app.api_encrypt_key');
            $cipher = config('app.cipher_aes');
            return openssl_decrypt($data, $cipher, $key, $options=0, $iv);           
        } catch(\Exception $e){   
            Log::info('Exception'.$e->getMessage());    
        } 
    }

    public function apiEncryptAES($data) {
        try{
            $key = config('app.api_encrypt_key');
            $iv = config('app.api_encrypt_key');
            $cipher = config('app.cipher_aes');
            return openssl_encrypt($data, $cipher, $key, $options=0, $iv);
        }catch(\Exception $e){
            Log::info('Exception'.$e->getMessage());
        }
    }

    public function getSflApiToken() {
        $token="";
        try {
            $redis = Redis::connection();
            $token = $redis->get('API_AUTH_TOKEN');
            if(empty($token)) {
                $url = config('api.sflApi')."/authenticate";
                Log::info('GetApiTokenUrl : '.$url);
                $response = $this->apiCall($url);
                Log::info('GetApiTokenResponse : '.json_encode($response));
                if(!empty($response)) {
                    $responseData =  empty($response->apiResponse) === false ? json_decode($response->apiResponse,true) : '';
                    if(!empty($responseData) && !empty($responseData['status']) && $responseData['status'] == 'success' && !empty($responseData['data']) && !empty($responseData['data']['token'])) {
                        $token = $responseData['data']['token'];
                        $redis->set('API_AUTH_TOKEN',$token, 'EX', 900);
                        Log::info('GetSflApiToken: '.$token);
                    }
                }
            }
        } catch(\Throwable | \Exception | ClientException $throwable) {
            Log::info('GetSflApiAuthorizationException: '.$throwable->getMessage());
        }
        $result['token'] = $token;
        return $result;
    }
}
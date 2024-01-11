<?php
namespace App\Util;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;

trait SecurityHelper
{
	
	public function encrypt($data)
	{
		$key = config('api.plEncKey');
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("aes-256-cbc"));
		$encrypted = openssl_encrypt($data, "aes-256-cbc", $key, 0, $iv);
		return  $encrypted . ':' . base64_encode($iv);
	}

	public function decrypt($data)
	{
		$key = config('api.plEncKey');
		$parts = explode(':', $data);
		if (empty($parts[1]) === false) {
			$decrypted = openssl_decrypt($parts[0], "aes-256-cbc", $key, 0, base64_decode($parts[1]));
		} else {
			$decrypted = $data;
		}
		
		return $decrypted;
	}

	public function cryptEncrypt($data)
	{
		return Crypt::encrypt($data);
	}

	public function cryptDecrypt($hash)
	{
		try {
		    $decrypted = Crypt::decrypt($hash);
		} catch (DecryptException $e) {
		    $decrypted = '';
		}

		return $decrypted;
	}
	public function plEncrypt($data)
    {
        try {
            $key = config('api.plEncryptionKey');
            $iv = config('api.plEncryptionIV');
            $cipher = config('api.plCipher');
            return openssl_encrypt($data, $cipher, $key, $options = 0, $iv);
        } catch (\Exception $e) {
            Log::info('Exception' . $e->getMessage());
        }
    }

    public function plDecrypt($data)
    {
        try {
            $key = config('api.plDecryptionKey');
            $iv = config('api.plDecryptionIV');
            $cipher = config('api.plCipher');

            return openssl_decrypt($data, $cipher, $key, $options = 0, $iv);
        } catch (\Exception $e) {
            Log::info('Exception' . $e->getMessage());
        }
    }
    public function glEncrypt($data)
    {
        try {
        	
            $key = config('api.glEncryptionKey');
            $iv = config('api.glEncryptionIV');
            $cipher = config('api.glCipher');
            return openssl_encrypt($data, $cipher, $key, $options = 0, $iv);
        } catch (\Exception $e) {
            Log::info('Exception' . $e->getMessage());
        }
    }

    public function glDecrypt($data)
    {
        try {
            $key = config('api.glDecryptionKey');
            $iv = config('api.glDecryptionIV');
            $cipher = config('api.glCipher');

            return openssl_decrypt($data, $cipher, $key, $options = 0, $iv);
        } catch (\Exception $e) {
            Log::info('Exception' . $e->getMessage());
        }
    }

    public function requestDecrypt($data) {
        
        $enc = config('api.encryption');
        if ($enc) {
            $decrypt = $this->commonDecrypt($data['data']);
            $result = json_decode($decrypt,true);
            return $result;
        } else {
            return $data;
        }
    }

    public function responseEncrypt($data) {
        $enc = config('api.encryption');
        if ($enc) {
            $data = json_encode($data);
            $result = $this->commonEncrypt($data);
            return $result;
        } else {
            return $data;
        }
    }

    public static function commonDecrypt($string)
    {
        $key = config('api.decryptKey');
        return openssl_decrypt(base64_decode($string), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $key);
    }

    public static function commonEncrypt($string)
    {
        $key = config('api.decryptKey');
        $data = openssl_encrypt($string, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $key);
        return base64_encode($data);
    }

    public static function cosEncrypt($data)
    {
        if(env('API_ENCRYPTION') == 1 && empty($data) === false && gettype($data) == "array") {
            $key  = env('CP_ENCRYPT_KEY');
            $iv   = env('CP_ENCRYPT_IV');
            Log::info('Decrypted Response - '.json_encode($data));
            $encryptedData = openssl_encrypt(json_encode($data), "AES-128-CBC", $key, 0, $iv);
            Log::info('Encrypted Response - '.json_encode($encryptedData));
            return $encryptedData;
        } 
        // else {
        //     Log::info('Decrypted Response - '.json_encode($data));
        //     return $data;
        // }
    }

    public static function cosDecrypt($data)
    {

        if(env('API_ENCRYPTION') == 1 && empty($data) === false && empty($data['data']) === false && gettype($data['data']) == "string") {
            $key  = env('CP_ENCRYPT_KEY');
            $iv   = env('CP_ENCRYPT_IV');
            Log::info('Encrypted Request - '.json_encode($data['data']));
            $decryptedData = json_decode(openssl_decrypt($data['data'], "AES-128-CBC", $key, 0, $iv),true);
            Log::info('Decrypted Request - '.json_encode($decryptedData));
            return $decryptedData;
        } 
        // else {
        //     Log::info('Decrypted Request - '.json_encode($data));
        //     return $data;
        // }
    }

    public static function cosEncryptString($data)
    {
        if(env('API_ENCRYPTION') == 1 && empty($data) === false && gettype($data) == "string") {
            $key  = env('CP_ENCRYPT_KEY');
            $iv   = env('CP_ENCRYPT_IV');
            Log::info('Decrypted Response - '.json_encode($data));
            $encryptedData = openssl_encrypt($data, "AES-128-CBC", $key, 0, $iv);
            Log::info('Encrypted Response - '.json_encode($encryptedData));
            return $encryptedData;
        } 
    }

    public static function cosDecryptString($data)
    {
        if(env('API_ENCRYPTION') == 1 && empty($data) === false) {
            $key  = env('CP_ENCRYPT_KEY');
            $iv   = env('CP_ENCRYPT_IV');
            Log::info('Encrypted Request - '.json_encode($data));
            $decryptedData = openssl_decrypt($data, "AES-128-CBC", $key, 0, $iv);
            Log::info('Decrypted Request - '.json_encode($decryptedData));
            return $decryptedData;
        } 
    }
}
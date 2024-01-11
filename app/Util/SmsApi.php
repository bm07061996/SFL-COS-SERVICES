<?php

namespace App\Util;

use Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Redis;
use App\Services\ValueFirstSmsService;
use App\Services\GupShupSms;
use App\Services\VivaConnectSmsService;
use App\Services\ValueFirstVoiceOtpService;
use App\Entities\PushSmsLogs;
use App\Entities\SMSMode;
use DateTime;

trait SmsApi {
    public function smsGateway($data) {
        try{
            \Log::info("Util SMS Gateway Helper >>>>>" .json_encode($data));
            $data['returnResponse'] = true;

            // viva connect request form 
            $vivaSms  = new VivaConnectSmsService;
            $vivatype = "VivaConnect";

            // value first request form 
            $valueSms  = new ValueFirstSmsService;
            $valuefirstType = "ValueFirst";

            // voice otp request form
            $valueFirstVoiceOtp = new ValueFirstVoiceOtpService;

            $response =[];
            $vivaSmsResponse=[];
            $valueSmsResponse=[];
            
            // Redis cache 
            $redis = Redis::connection();
            $redisresponse = $redis->get('STFC-SMS-FAILED');
            if(empty($data['otp_action']) === false && $data['otp_action'] == "send"){
                $redisresponse = empty($redisresponse) === false && $redisresponse =='valuefirst' ? 'vivaconnect' : 'vivaconnect';
            }
            if(empty($data['otp_action']) === false && $data['otp_action'] == "resend"){
                $redisresponse = empty($redisresponse) === false && $redisresponse=='vivaconnect' ? 'valuefirst' : 'valuefirst';
            }
            if(empty($data['otp_action']) === false && $data['otp_action'] == "voiceotp"){
                $redisresponse = 'voiceotp';
            }

            $smsMode = SMSMode::first();
            $redisresponse = (empty($smsMode) === false && empty($smsMode->sms_mode) === false) ? $smsMode->sms_mode : 'vivaconnect';
            // Check the Sms Logs Sent Before 15 minutes
            $date = new DateTime;
            $date->modify('-15 minutes');
            $formatted_date = $date->format('Y-m-d H:i:s');
            $recSentSmsLogCnt = PushSmsLogs::select('*')->where('mobile',$data['mobile'])->where('product_id',1)->where('created_at','>=',$formatted_date)->orderBy('id', 'desc')->count();
            if(empty($redisresponse) === false && $redisresponse =='vivaconnect' || $data['otp_action'] == 'send'){
                \Log::info("redis sms response - " . $redisresponse);
                $data['type'] = $vivatype;
                \Log::info('Value First req data - '.json_encode($data));

                if ($recSentSmsLogCnt >3){
                    $vivaSmsResponse['checkCntLimit'] = 1;
                    $vivaSmsResponse['message'] = "Oops. It looks like you've reached the limit for OTP requests.Try again in 15 mins.";
                    return $vivaSmsResponse;

                }else{
                    $vivaSmsResponse = $vivaSms->processSmsService($data);
                    \Log::info('Viva Connect Resonse - '.json_encode($vivaSmsResponse));
                    if($vivaSmsResponse['message'] === 'Otp sent successfully'){
                        return $vivaSmsResponse;
                    }
                }

                if($vivaSmsResponse['message'] === 'Sms Push Failed'){
                    $data['type'] = $valuefirstType;
                    if ($recSentSmsLogCnt >3){
                         $valueSmsResponse['checkCntLimit'] = 1;
                         $valueSmsResponse['message'] = "Oops. It looks like you've reached the limit for OTP requests.Try again in 15 mins.";
                         return $valueSmsResponse;
                     }else{
                        $valueSmsResponse = $valueSms->processSmsService($data);
                        \Log::info('Value First Resonse - '.json_encode($valueSmsResponse));
                        return $valueSmsResponse;
                    }
                }
            } else if(empty($redisresponse===false) && $redisresponse=='valuefirst' || $data['otp_action'] == 'resend'){
                \Log::info("redis sms response failed viva  - " . $redisresponse);
                $data['type'] = $valuefirstType;
                if ($recSentSmsLogCnt >3){
                    $valueSmsResponse['checkCntLimit'] = 1;
                    $valueSmsResponse['message'] = "Oops. It looks like you've reached the limit for OTP requests.Try again in 15 mins.";
                    return $valueSmsResponse;
                }else{
                    $valueSmsResponse = $valueSms->processSmsService($data);
                    \Log::info('Value First Resonse - '.json_encode($valueSmsResponse));
                    if($valueSmsResponse['message'] === 'Otp sent successfully'){
                        return $valueSmsResponse;
                    }
                }
                if($valueSmsResponse['message'] === 'Sms Push Failed'){
                    $data['type'] = $vivatype;
                    if ($recSentSmsLogCnt >3){
                        $vivaSmsResponse['checkCntLimit'] = 1;
                        $vivaSmsResponse['message'] = "Oops. It looks like you've reached the limit for OTP requests.Try again in 15 mins.";
                        return $vivaSmsResponse;
                    }else{
                        $vivaSmsResponse = $vivaSms->processSmsService($data); 
                        \Log::info('Viva Connect Resonse - '.json_encode($vivaSmsResponse));
                        return $vivaSmsResponse;
                    }
                }
            } else if(empty($redisresponse===false) && $redisresponse=='voiceotp' || $data['otp_action'] == 'voiceotp'){
                    $voiceOtpReponse = $valueFirstVoiceOtp->processSmsService($data); 
                    \Log::info('Voice Otp Resonse - '.json_encode($voiceOtpReponse));
                    return $voiceOtpReponse;
            }
            $response['message'] === 'Sms Push Failed';
        }catch(\Throwable | \Exception | \ClientException $throwable) {
            \log::info($throwable->getMessage());
            $response['message'] === 'Sms Push Failed';
        } 
        return $response;
    }

    public function dropOffSmsGateway($data) {
        //$data['otpAction'] = "send";
        try{
            $data['returnResponse'] = true;
            // gupshup sms request form 
            $gupshupSms  = new GupShupSms;
            $gupshupSmstype = "gupshupSms";

            // value first request form 
            $valueSms  = new ValueFirstSmsService;
            $valuefirstType = "ValueFirst";

            // voice otp request form
            $valueFirstVoiceOtp = new ValueFirstVoiceOtpService;
            // viva connect request form
            $vivaSms  = new VivaConnectSmsService;
            $vivatype = "VivaConnect";

            $response =[];
            $vivaSmsResponse=[];
            $valueSmsResponse=[];
            
            // Redis cache 
            $redis = Redis::connection();
            $redisresponse = $redis->get('SFL-SMS-FAILED');
            $redisresponse = empty($redisresponse) === false && $redisresponse =='gupshupSms' ? 'vivaconnect' : 'gupshupSms';
            $smsMode = SMSMode::first();
            $redisresponse = (empty($smsMode) === false && empty($smsMode->sms_mode) === false) ? $smsMode->sms_mode : 'vivaconnect';
            // Check the Sms Logs Sent Before 15 minutes
            $date = new DateTime;
            $date->modify('-15 minutes');
            $formatted_date = $date->format('Y-m-d H:i:s');
            $recSentSmsLogCnt = PushSmsLogs::select('*')->where('mobile',$data['mobileNo'])->where('product_id',1)->where('created_at','>=',$formatted_date)->orderBy('id', 'desc')->count();
            
            if(empty($redisresponse) === false && $redisresponse =='gupshupSms'){
                $data['type'] = $gupshupSmstype;
                $gupshupSmsResponse = $gupshupSms->processSmsService($data);
                if($gupshupSmsResponse['message'] === 'Sms Push Failed'){
                    $data['type'] = $vivatype;
                    $vivaSmsResponse = $vivaSms->processSmsService($data);
                    return $vivaSmsResponse;
                }
                if($gupshupSmsResponse['message'] === 'Otp sent successfully'){
                    return $gupshupSmsResponse;
                }                
            } else if(empty($redisresponse) === false && $redisresponse =='vivaconnect') {
                $data['type'] = $vivatype;
                $vivaSmsResponse = $vivaSms->processSmsService($data);
                if($vivaSmsResponse['message'] === 'Sms Push Failed'){
                    $data['type'] = $gupshupSmstype;
                    $gupshupSmsResponse = $gupshupSms->processSmsService($data);
                    return $gupshupSmsResponse;
                }
                if($vivaSmsResponse['message'] === 'Otp sent successfully'){
                    return $vivaSmsResponse;
                } 
            }
            $response['message'] === 'Sms Push Failed';            
        }catch(\Throwable | \Exception | \ClientException $throwable) {
            \log::info('Dropoff Sms gateway - '.$throwable->getMessage());
            $response['message'] === 'Sms Push Failed';
        }
        return $response;
    }
}
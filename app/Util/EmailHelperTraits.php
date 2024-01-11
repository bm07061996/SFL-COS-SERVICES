<?php

namespace App\Util;
use Carbon\Carbon;
use App\Entities\ApiExceptionEmailLogs;
use App\Entities\GiApiExceptionEmailLogs;
use App\Entities\ApiExceptionLogs;
use App\Entities\PostLoginApiExceptionEmailLogs;
use App\Entities\CpApiExceptionEmailLogsCollection;
use DateTime;

trait EmailHelperTraits
{
    public function model()
    {
        return ApiExceptionLogs::class;
    }

	public function ExceptionEmailInitiate($data)
    {
        try {     
                // email trigger
                $message = array(
                    'API' => $data['api_name'],
                    'Api request' => json_encode($data['request']));
                app()->configure('mail');
                $emailParams['from'] = env('MAIL_FROM_DEPOSIT_CRM');
                $emailParams['fromName'] = env('MAIL_FROM_NAME');        
                if(env('APP_ENV') == 'production'){
                $emailParams['to'] = config('mail.prodExcToApi');
                }else{
                    $emailParams['to'] = config('mail.excToApi'); 
                }
                $data['to'] = $emailParams['to'];
                $emailParams['subject'] = "Regarding API Exception";
                $emailParams['message'] = $message;
                $exceptionLogInsert = $this->apiExceptionLogInsert($data, $emailParams);
        } catch(\Throwable | \Exception | \ClientException $throwable) {
            \Log::info("API exception email trigger input" . json_encode($emailParams));
            \Log::info($throwable->getMessage());
        }
    }
    public function apiExceptionLogInsert($data, $emailParams)
    {

        $date = date("Y-m-d H:i:s");
        $to_email_id = implode(',',$data['to']);
        $exceptionQuery = \App\Entities\ApiExceptionEmailLogs::query();
        $apiException = $exceptionQuery->insert([
            "api_name" => $data['api_name'],
            "request" => json_encode($data['request']),
            "to_email_id" => $to_email_id,
            "exception" => $data['exception'],
            "created_at" => $date
        ]);
        $emailResponse = $this->ExceptionsendingEmail($emailParams);
    }
    public function ExceptionsendingEmail($data)
    {
        try{
            if (empty($data['from']) === false && empty($data['to']) === false && empty($data['message']) === false) {
                $emailParams['from'] = $data['from'];
                $emailParams['fromName'] = $data['fromName'];
                $emailParams['to'] = $data['to'];
                $emailParams['subject'] = $data['subject'];
                $emailParams['template'] = $data['message'];
                $email = $this->ExceptionemailTrigger($emailParams);
                return true;
            }else{
                \Log::info("API Exception email sending failed - Invalid request ..." .json_encode($data));
                return false;
            }
        }catch(\Throwable | \Exception | \ClientException $throwable) {
            \Log::info("API exception email failed" . json_encode($emailParams));
            \Log::info($throwable->getMessage());
        }
    } 
    public function ExceptionemailTrigger($emailParams)
    {
    	if(empty($emailParams['from'])===true || empty($emailParams['fromName'])===true || empty($emailParams['subject'])===true || empty($emailParams['template'])===true || empty($emailParams['to'])===true) {
    		return false;
    	}
        $msg = $this->Exceptionsmtp($emailParams);
        return $msg;
    }
    
    public function Exceptionsmtp($data)
    {
        app()->configure('mail');
        try{
                $bodyContent = '';
                if( empty($data['template']) === false) {
                    foreach($data['template'] as $key => $value) {
                        $bodyContent .= '<p>'.ucfirst($key).' : '.$value.'</p></br>';
                    }
                }
                $body = '<html><body>'.$bodyContent.'<p>Issue : Service down</p></body></html>';
          
                $transport = (new \Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'),env('MAIL_ENCRYPTION')))
                                ->setUsername(env('MAIL_USERNAME'))
                                ->setPassword(env('MAIL_PASSWORD'));					
                $mailer = new \Swift_Mailer($transport);
                $message = (new \Swift_Message($data['subject']))
                ->setFrom([$data['from'] => $data['from']])
                ->setTo($data['to'])
                ->setBody($body,'text/html');			
                if( empty( $data['cc'] ) === false ) {
                    $message->setCc($data['cc']);
                }
                if( empty( $data['bcc'] ) === false ) {
                    $message->setBcc($data['bcc']);
                }
                return $mailer->send($message);
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());            
        }       
        return true;    
    }


    public function GiExceptionEmailInitiate($data){
        try {                
            // email trigger
            $message = array(
                'USERID' => empty($data) === false && empty($data['userId']) === false ? $data['userId'] : '',
                'API' => empty($data) === false && empty($data['api_name']) === false ? $data['api_name'] : '',
                'API' => empty($data) === false && empty($data['api_url']) === false ? $data['api_url'] : '',
                'Api request' => empty($data) === false && empty($data['request_data']) === false ? json_encode($data['request_data']) : ''
            );
            
            app()->configure('mail');
            $emailParams['from'] = env('MAIL_FROM_ADDRESS');
            $emailParams['fromName'] = env('MAIL_FROM_NAME');        
            if(env('APP_ENV') == 'production'){
            $emailParams['to'] = config('mail.giprodExcToApi');
            }else{
                $emailParams['to'] = config('mail.giexcToApi'); 
            }
            $data['to'] = $emailParams['to'];
            $emailParams['subject'] = "General Insurance API Exception";
            $emailParams['message'] = $message;
            $exceptionLogInsert = $this->giapiExceptionLogInsert($data, $emailParams);
        } catch(\Throwable | \Exception | \ClientException $throwable) {
            \Log::info("API exception email trigger input" . json_encode($emailParams));
            \Log::info($throwable->getMessage());
        }
    }

    public function giapiExceptionLogInsert($data, $emailParams)
    {
        $date = date("Y-m-d H:i:s");
        $to_email_id = empty($data) === false && empty($data['to']) === false ? implode(',',$data['to']) : '';
        $exceptionQuery = \App\Entities\GiApiExceptionEmailLogs::query();
        $exceptionlogarray =array(
            "userId" => empty($data) === false && empty($data['userId']) === false ? $data['userId'] : '',
            "refrenceId" => empty($data) === false && empty($data['refrenceId']) === false ? $data['refrenceId'] : '',
            "api_name" => empty($data) === false && empty($data['api_name']) === false ? $data['api_name']: '',
            "request" => empty($data) === false && empty($data['request_data']) === false ? json_encode($data['request_data']) : '',
            "to_email_id" => $to_email_id,
            "exception" => empty($data) === false && empty($data['exception']) === false ?$data['exception'] : '', 
            "created_at" => $date
        );
    
        $response = $exceptionQuery->insertGetId($exceptionlogarray);
        $emailResponse = $this->GiExceptionsendingEmail($emailParams);
    }

    public function GiExceptionsendingEmail($data)
    {
        try{
            if (empty($data['from']) === false && empty($data['to']) === false && empty($data['message']) === false) {
                $emailParams['from'] = $data['from'];
                $emailParams['fromName'] = $data['fromName'];
                $emailParams['to'] = $data['to'];
                $emailParams['subject'] = $data['subject'];
                $emailParams['template'] = $data['message'];
                $email = $this->GiExceptionemailTrigger($emailParams);
                return true;
            }else{
                \Log::info("API Exception email sending failed - Invalid request ..." .json_encode($data));
                return false;
            }
        }catch(\Throwable | \Exception | \ClientException $throwable) {
            \Log::info("API exception email failed" . json_encode($emailParams));
            \Log::info($throwable->getMessage());
        }
    } 

    public function GiExceptionemailTrigger($emailParams)
    {
    	if(empty($emailParams['from'])===true || empty($emailParams['fromName'])===true || empty($emailParams['subject'])===true || empty($emailParams['template'])===true || empty($emailParams['to'])===true) {
    		return false;
    	}
        $msg = $this->GiExceptionsmtp($emailParams);
        return $msg;
    }

    public function GiExceptionsmtp($data)
    {
        app()->configure('mail');
        try{
                $bodyContent = '';
                if( empty($data['template']) === false) {
                    foreach($data['template'] as $key => $value) {
                        $bodyContent .= '<p>'.ucfirst($key).' : '.$value.'</p></br>';
                    }
                }
                $body = '<html><body>'.$bodyContent.'<p>Issue :General Insurance API Exception Error </p></body></html>';
          
                $transport = (new \Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'),env('MAIL_ENCRYPTION')))
                                ->setUsername(env('MAIL_USERNAME'))
                                ->setPassword(env('MAIL_PASSWORD'));					
                $mailer = new \Swift_Mailer($transport);
                $message = (new \Swift_Message($data['subject']))
                ->setFrom([$data['from'] => $data['from']])
                ->setTo($data['to'])
                ->setBody($body,'text/html');			
                if( empty( $data['cc'] ) === false ) {
                    $message->setCc($data['cc']);
                }
                if( empty( $data['bcc'] ) === false ) {
                    $message->setBcc($data['bcc']);
                }
                return $mailer->send($message);
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());            
        }       
        return true;    
    }

    //exception logs for FD

    public function FdExceptionEmailInitiate($data){
        try {     
            
            // email trigger
            $message = array(
                'API' => empty($data) === false && empty($data['api_name']) === false ? $data['api_name'] : '',
                // 'API' => empty($data) === false && empty($data['api_url']) === false ? $data['api_url'] : '',
                'Api request' => empty($data) === false && empty($data['request_data']) === false ? json_encode($data['request_data']) : ''
            );
            
            app()->configure('mail');
            $emailParams['from'] = env('MAIL_FROM_ADDRESS');
            $emailParams['fromName'] = env('MAIL_FROM_NAME');        
            if(env('APP_ENV') == 'production'){
                $emailParams['to'] = config('mail.fdprodExcToApi');
            }else{
                $emailParams['to'] = config('mail.fdexcToApi'); 
            }
            $data['to'] = $emailParams['to'];
            $emailParams['subject'] = "Fixed Deposit API Exception";
            $emailParams['message'] = $message;
            $exceptionLogInsert = $this->fdapiExceptionLogInsert($data, $emailParams);
        } catch(\Throwable | \Exception | \ClientException $throwable) {
            \Log::info("API exception email trigger input" . json_encode($emailParams));
            \Log::info($throwable->getMessage());
        }
    }

    public function fdapiExceptionLogInsert($data, $emailParams)
    {
        $date = date("Y-m-d H:i:s");
        $emailParams['data'] = $data;
        $to_email_id = empty($data) === false && empty($data['to']) === false ? implode(',',$data['to']) : '';
        $exceptionQuery = \App\Entities\ApiExceptionLogs::query();
        $exceptionlogarray =array(
            
            "api_name" => empty($data) === false && empty($data['api_name']) === false ? $data['api_name']: '',
            "request" => empty($data) === false && empty($data['request_data']) === false ? json_encode($data['request_data']) : '',
            "to_email_id" => $to_email_id,
            "exception" => empty($data) === false && empty($data['exception']) === false ?$data['exception'] : '', 
            "created_at" => $date
        );
        // "userId" => empty($data) === false && empty($data['userId']) === false ? $data['userId'] : '',
        // "refrenceId" => empty($data) === false && empty($data['refrenceId']) === false ? $data['refrenceId'] : '',
        
        // $response = $exceptionQuery->insertGetId($exceptionlogarray);
        $ApiExceptionLogs=new ApiExceptionLogs; 
        $ApiExceptionLogs->fill($exceptionlogarray)->save();
        
        $emailResponse = $this->FdExceptionsendingEmail($emailParams);
    }

    public function FdExceptionsendingEmail($data)
    {
        try{
            $errorMessage = empty($data['data']['error']) === false ? $data['data']['error'] : ""; 
            if (empty($data['from']) === false && empty($data['to']) === false && empty($data['message']) === false) {
                $emailParams['from'] = $data['from'];
                $emailParams['fromName'] = $data['fromName'];
                $emailParams['to'] = $data['to'];
                $emailParams['subject'] = $data['subject'];
                $emailParams['template'] = $data['message'];
                $emailParams['errorMessage'] = $errorMessage;
                //print_r($emailParams);die;
                $email = $this->FdExceptionemailTrigger($emailParams);
                return true;
            }else{
                \Log::info("API Exception email sending failed - Invalid request ..." .json_encode($data));
                return false;
            }
        }catch(\Throwable | \Exception | \ClientException $throwable) {
            \Log::info("API exception email failed" . json_encode($emailParams));
            \Log::info($throwable->getMessage());
        }
    } 

    public function FdExceptionemailTrigger($emailParams)
    {
    	if(empty($emailParams['from'])===true || empty($emailParams['fromName'])===true || empty($emailParams['subject'])===true || empty($emailParams['template'])===true || empty($emailParams['to'])===true) {
    		return false;
    	}
        $msg = $this->FdExceptionsmtp($emailParams);
        return $msg;
    }

    public function FdExceptionsmtp($data)
    {
        app()->configure('mail');
        try{
                $bodyContent = '';
               // print_r($data['errorMessage']['message']);die;
                // if(empty($data['errorMessage']) === false && empty($data['errorMessage']['FaultMessage']) === false  && empty($data['errorMessage']['message']) === false){
                //         $bodyContent .= '<p>Error : '.$data['errorMessage']['FaultMessage'].'</p></br><p>Error : '.$data['errorMessage']['message'].'</p></br>';
                // }else{
                    foreach($data['errorMessage']->getTrace() as $t){
                        if(isset($t['file']) && isset($t['line'])){
                            $bodyContent .= '<p style="margin:10px 0;">'.addslashes($t['file']).' @ '.addslashes($t['line']).'</p>';
                        }
                    }
                    
                    if( empty($data['errorMessage']->getMessage()) === false) {
                        $bodyContent .= '<p>Error : '.$data['errorMessage']->getMessage().'</p></br>';
                    }
                //}

                $body = '<html><body>'.$bodyContent.'<p>Issue :Fixed Deposit API Exception Error </p></body></html>';

                $transport = (new \Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'),env('MAIL_ENCRYPTION')))
                                    ->setUsername(env('MAIL_USERNAME'))
                                    ->setPassword(env('MAIL_PASSWORD'));                    
                $mailer = new \Swift_Mailer($transport);
                $message = (new \Swift_Message($data['subject']))
                        ->setFrom([env('MAIL_FROM_ADDRESS') => env('MAIL_FROM_ADDRESS')])
                        ->setTo($data['to'][0])
                        ->setBody($body,'text/html'); 
                return $mailer->send($message);
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());            
        }       
        return true;    
    }


    public function sendMailGeneratePDf($email,$htmlToPdf){
        try{     
            $data['subject'] = "Receipt of Online Deposit";
            $data['from'] = env('MAIL_FROM_ADDRESS');
            $data['to'] = $email;
            app()->configure('mail');
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadHtml($htmlToPdf['html']);
            $filename = "Receipt_".time().'.pdf';
            
            $attachment = \Swift_Attachment::newInstance($pdf->output(), $filename, 'application/pdf');
            $transport = (new \Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'),env('MAIL_ENCRYPTION')))
                                    ->setUsername(env('MAIL_USERNAME'))
                                    ->setPassword(env('MAIL_PASSWORD'));                    
            $mailer = new \Swift_Mailer($transport);
            $message = (new \Swift_Message($data['subject']))
                        ->setFrom([env('MAIL_FROM_ADDRESS') => env('MAIL_FROM_ADDRESS')])
                        ->setTo($data['to'])
                        ->attach($attachment)
                        ->setBody($htmlToPdf['template'],'text/html');           
                   
            $mailer->send($message);
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());   
            \log::info('Receiptss'.$e->getMessage());         
        }       
        return $pdf->download($filename);  
    }

    public function sendPaymentSucessMail($email,$bodyContent,$pdfBodyContent){
        try{
            app()->configure('mail');
            $body = '<html><body><p>'.$bodyContent.'</p></body></html>';
            $pdfBody = '<html><body><p>'.$pdfBodyContent.'</p></body></html>';
            $data['subject'] = "Payment Succesfull";
            $data['from'] = env('MAIL_FROM_ADDRESS');
            $data['to'] = $email;
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadHtml($pdfBody);
            $filename = "PaymentReceipt_".time().'.pdf';
            
            $attachment = \Swift_Attachment::newInstance($pdf->output(), $filename, 'application/pdf');
            $transport = (new \Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'),env('MAIL_ENCRYPTION')))
                                    ->setUsername(env('MAIL_USERNAME'))
                                    ->setPassword(env('MAIL_PASSWORD'));                    
            $mailer = new \Swift_Mailer($transport);
            $message = (new \Swift_Message($data['subject']))
                        ->setFrom([env('MAIL_FROM_ADDRESS') => env('MAIL_FROM_ADDRESS')])
                        ->setTo($data['to'])
                        ->attach($attachment)
                        ->setBody($body,'text/html');           
                   
            $mailer->send($message);
            return $pdf->download($filename);  
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());   
            \log::info('Receiptss'.$e->getMessage());         
        }
         
    }

    public function sendAcknowledgementMail($email,$bodyContent,$pdfBodyContent){
        try{
            app()->configure('mail');
            $body = '<html><body><p>'.$bodyContent.'</p></body></html>';
            $pdfBody = '<html><body><p>'.$pdfBodyContent.'</p></body></html>';
            $data['subject'] = "Fixed Deposit Booking Confirmation";
            $data['from'] = env('MAIL_FROM_ADDRESS');
            $data['to'] = $email;
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadHtml($pdfBody);
            $filename = "Acknowledgement_".time().'.pdf';
            
            $attachment = \Swift_Attachment::newInstance($pdf->output(), $filename, 'application/pdf');
            $transport = (new \Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'),env('MAIL_ENCRYPTION')))
                                    ->setUsername(env('MAIL_USERNAME'))
                                    ->setPassword(env('MAIL_PASSWORD'));                    
            $mailer = new \Swift_Mailer($transport);
            $message = (new \Swift_Message($data['subject']))
                        ->setFrom([env('MAIL_FROM_ADDRESS') => env('MAIL_FROM_ADDRESS')])
                        ->setTo($data['to'])
                        ->attach($attachment)
                        ->setBody($body,'text/html');           
                   
            $mailer->send($message);
            return $pdf->download($filename);  
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());   
            \log::info('Receiptss'.$e->getMessage());         
        }
    }

    public function sendPaymentFailureMail($email,$bodyContent){
        try{
            app()->configure('mail');
            $body = '<html><body><p>'.$bodyContent.'</p></body></html>';
            $data['subject'] = "Your Fixed Deposit payment has failed";
            $data['from'] = env('MAIL_FROM_ADDRESS');
            $data['to'] = $email;
            $transport = (new \Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'),env('MAIL_ENCRYPTION')))
                                    ->setUsername(env('MAIL_USERNAME'))
                                    ->setPassword(env('MAIL_PASSWORD'));                    
            $mailer = new \Swift_Mailer($transport);
            $message = (new \Swift_Message($data['subject']))
                        ->setFrom([env('MAIL_FROM_ADDRESS') => env('MAIL_FROM_ADDRESS')])
                        ->setTo($data['to'])
                        ->setBody($body,'text/html');           
                   
            return $mailer->send($message);  
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());   
            \log::info('Receiptss'.$e->getMessage());         
        }
         
    }

    public function PostLoginExceptionEmailInitiate($data){
        try {     
            // email trigger
            $message = array(
                'API NAME' => empty($data) === false && empty($data['api_name']) === false ? $data['api_name'] : '',
                'API URL' => empty($data) === false && empty($data['url']) === false ? $data['url'] : '',
                'Api request' => empty($data) === false && empty($data['api_req']) === false ? json_encode($data['api_req']) : '',
                'Api Decrypt request' => empty($data) === false && empty($data['front_req']) === false ? json_encode($data['front_req']) : ''
            );
            
            app()->configure('mail');
            $emailParams['from'] = env('MAIL_FROM_ADDRESS');
            $emailParams['fromName'] = env('MAIL_FROM_NAME');        
            if(env('APP_ENV') == 'production'){
                $emailParams['to'] = config('mail.postprodExcToApi');
                $emailParams['subject'] = "LIVE Post Login API Exception";
            }else{
                $emailParams['to'] = config('mail.postexcToApi'); 
                $emailParams['subject'] = "UAT Post Login API Exception";
            }
            $data['to'] = $emailParams['to'];
            //$emailParams['subject'] = "Post Login API Exception";
            $emailParams['message'] = $message;
            $exceptionLogInsert = $this->PostLoginapiExceptionLogInsert($data, $emailParams);
        } catch(\Throwable | \Exception | \ClientException $throwable) {
            \Log::info("API exception email trigger input" . json_encode($emailParams));
            \Log::info($throwable->getMessage());
        }
    }

    public function PostLoginapiExceptionLogInsert($data, $emailParams) {
        $date = date("Y-m-d H:i:s");
        $to_email_id = empty($data) === false && empty($data['to']) === false ? implode(',',$data['to']) : NULL;
        $exceptionQuery = CpApiExceptionEmailLogsCollection::query();
        $exceptionlogarray =array(
            "api_name"      => empty($data) === false && empty($data['api_name']) === false ? $data['api_name']: NULL,
            "request"       => empty($data) === false && empty($data['api_req']) === false ? json_encode($data['api_req']) : NULL,
            "to_email_id"   => $to_email_id,
            "request_timestamp" => date('Y-m-d H:i:s'.substr((string)microtime(), 1, 4).'\Z'),
            "response_timestamp" => date('Y-m-d H:i:s'.substr((string)microtime(), 1, 4).'\Z'),
            "status"     => "success",
            "reason"        => NULL,
            "exception"     => empty($data) === false && empty($data['exception']) === false ?$data['exception'] : NULL, 
            "created_at"    => $date,
        );
        $exceptionQuery->insertGetId($exceptionlogarray);
        $emailResponse = $this->PostLoginExceptionsendingEmail($emailParams);
    }

    public function PostLoginExceptionsendingEmail($data)
    {
        try{
            if (empty($data['from']) === false && empty($data['to']) === false && empty($data['message']) === false) {
                $emailParams['from'] = $data['from'];
                $emailParams['fromName'] = $data['fromName'];
                $emailParams['to'] = $data['to'];
                $emailParams['subject'] = $data['subject'];
                $emailParams['template'] = $data['message'];
                $email = $this->PostLoginExceptionemailTrigger($emailParams);
                return true;
            }else{
                \Log::info("API Exception email sending failed - Invalid request ..." .json_encode($data));
                return false;
            }
        }catch(\Throwable | \Exception | \ClientException $throwable) {
            \Log::info("API exception email failed" . json_encode($emailParams));
            \Log::info($throwable->getMessage());
        }
    } 
    public function PostLoginExceptionemailTrigger($emailParams)
    {
    	if(empty($emailParams['from'])===true || empty($emailParams['fromName'])===true || empty($emailParams['subject'])===true || empty($emailParams['template'])===true || empty($emailParams['to'])===true) {
    		return false;
    	}
        $msg = $this->PostLoginExceptionsmtp($emailParams);
        return $msg;
    }
    
    public function PostLoginExceptionsmtp($data)
    {
        app()->configure('mail');
        try{
                $bodyContent = '';
                if( empty($data['template']) === false) {
                    foreach($data['template'] as $key => $value) {
                        $bodyContent .= '<p>'.ucfirst($key).' : '.$value.'</p></br>';
                    }
                }
                $body = '<html><body>'.$bodyContent.'<p>Issue : PostLogin API Exception Error</p></body></html>';
          
                $transport = (new \Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'),env('MAIL_ENCRYPTION')))
                                ->setUsername(env('MAIL_USERNAME'))
                                ->setPassword(env('MAIL_PASSWORD'));					
                $mailer = new \Swift_Mailer($transport);
                $message = (new \Swift_Message($data['subject']))
                ->setFrom([$data['from'] => $data['from']])
                ->setTo($data['to'])
                ->setBody($body,'text/html');			
                if( empty( $data['cc'] ) === false ) {
                    $message->setCc($data['cc']);
                }
                if( empty( $data['bcc'] ) === false ) {
                    $message->setBcc($data['bcc']);
                }
                return $mailer->send($message);
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());            
        }       
        return true;    
    }

}
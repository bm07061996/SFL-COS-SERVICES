<?php

namespace App\Component\PostLogin\PostLoginProcess;

use App\Util\Utils;
use App\Entities\Page;
use App\Util\RedisHelper;
use App\Component\ComponentHelperTraits;
use App\Repository\EmailLogRepo;
use Validator;
use Log;

abstract class PostLoginProcessAbstract
{

    use RedisHelper, ComponentHelperTraits;

	/**array
     * All of the Response.
     *
     * @var 
     */
    public $response = [];

    /**
     * All of the Data from the request as well as internal data injection.
     *
     * @var array
     */
    protected $data = [];
    
    public $subscription_status = false;

    /**
     * Register a set of routes with a set of shared attributes.
     *
     * @param  array  $data
     * @return void
     */

	public function __construct(array $data)
	{
        $this->data = $data;
	}

    abstract public function process();

    public function getValidationRules()
    {
        $rules = config('validationrules.' . $this->data['type'] . '.' . $this->data['action']);

        return $rules;
    }

    public function getUrlDetails($slug)
    {
        $urlData = Page::where("url", kebab_case($slug))->first();
        $data = [];

        if (empty($urlData->module) === false) {

            if (empty($urlData->productSlug) === false) {
                $data['details']['product_slug'] = $urlData->productSlug;
            }
            if (empty($urlData->offerId) === false) {
                $data['details']['lender_offer_id'] = $urlData->offerId;
            }
            if (empty($urlData->type) === false) {
                $data['type'] = $urlData->type;
            }
            $data['module'] = $urlData->module;
        }

        return $data;
    }

    public function getUserDetails($userDetails)
    {
        $userDetails['mobile'] = empty($userDetails['mobile']) === false ? aesEncrypt($userDetails['mobile']) : "";
        $userDetails['email'] = empty($userDetails['email']) === false ? aesEncrypt($userDetails['email']) : "";

        return array_filter($userDetails);
    }
    // public function sendEmail($data)
    // {
    //     try{
    //         $noFromCheck = "0";
    //         if(empty($this->data['message']['requestType']) === false && empty($this->data['message']['customer']) === false && strtolower($this->data['message']['requestType']) == "general" && strtolower($this->data['message']['customer']) == "existing customer"){
    //             $noFromCheck = "1";
    //         }
    //         if ($noFromCheck == "1" || empty($data['from']) === false && empty($data['message']) === false) {
    //             $messageData = $data['message'];
    //             app()->configure('mail');

    //             $emailParams = [];
    //             $emailParams['from'] = $data['from'];
    //             $emailParams['fromName'] = $data['name'];
    //             $emailParams['subject'] = $data['subject'];
    //             $emailParams['template'] = $data['message'];
    //             $emailParams['keyCheck'] = "no";

    //             if(empty($data['requestType']) === false && "Deposits" == $data['requestType']) {
    //             	$emailParams['to'] = config('mail.toCustomerSupport');
    //             	$emailParams['bcc'] = config('mail.toBopanna');
    //             }else if(empty($data['requestType']) === false && "Media" == $data['requestType']){
    //             	$emailParams['to'] = config('mail.toMedia');
    //             }else if(empty($data['requestType']) === false && "NCD's" == $data['requestType']){
    //             	$emailParams['to'] = config('mail.toCustomerSupport');
    //             	$emailParams['cc'] = config('mail.toNCDs');
    //             	$emailParams['bcc'] = config('mail.toBopanna');
    //             }else if(empty($data['requestType']) === false && "Secretarial" == $data['requestType']){
    //             	$emailParams['to'] = config('mail.toSecretarial');
    //             }else if(empty($data['requestType']) === false && "Loan Products" == $data['requestType']){
    //             	$emailParams['to'] = config('mail.toLoanProducts');
    //             }else{
    //             	$emailParams['to'] = config('mail.toContactUs');
    //             }
    //             // $emailParams['to'] = 'sujana.n@novactech.in';
    //             \Log::info("emailParams Response - " . json_encode($emailParams));
    //             $email = false;
    //             try {
	// 				$email = $this->emailTrigger($emailParams);
	// 				\Log::info("email triggered Response - " . json_encode($email));
	// 			} catch(\Throwable | \Exception | \ClientException $throwable) {
	// 				\Log::info($throwable->getMessage());
	// 			}
    //             $emaillog['email'] =  empty($email) === false && $email == 1 || $email == true ? "success" : "failure";
	// 			$emaillog['throwable'] = empty($throwable) === false ? json_encode($throwable) : "";
	// 			$emaillog['data'] = json_encode($this->data);
	// 			$emailLogRep = app()->make(EmailLogRepo::class);
	// 			$emailLogRep->emailLogInsertion($emaillog);
    //             return true;
    //         }else{
    //             \Log::info("Email sending failed - Invalid request ..." .json_encode($data));
    //             return false;
    //         }
    //     } catch(\Throwable | \Exception | \ClientException $throwable) {
    //         \Log::info("Email sending failed" . json_encode($emailParams));
    //         \Log::info($throwable->getMessage());
    //     }
    // }

           
}

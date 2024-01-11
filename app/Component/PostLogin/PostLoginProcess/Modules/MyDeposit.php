<?php

namespace App\Component\PostLogin\PostLoginProcess\Modules;

use App\Component\PostLogin\PostLoginProcess\PostLoginProcessAbstract;
use App\Component\PostLogin\PostLoginProcess\PostLoginProcessInterface;
use App\Util\Api;
use App\Util\EmailHelperTraits;
use App\Component\ComponentHelperTraits;
use Illuminate\Support\Facades\Log;
use App\Entities\FdPincodeMaster;

class MyDeposit extends PostLoginProcessAbstract implements PostLoginProcessInterface
{   
    use Api, EmailHelperTraits, ComponentHelperTraits;

	public function process()
	{
        $request = $this->data;
        $data = empty($this->data['data']) === false ? $this->data['data'] : "";
        if(strtolower($request['action']) =="getpincodedetails"){
            $this->response = $this->getPincodeDetails($data);
        } 
	    return $this;
	}
    public function getPincodeDetails($decryptReqData) {
        try{
            $pincode   = empty($decryptReqData['pincode']) === false ? $decryptReqData['pincode'] : '';
            if ($pincode != ""){
                $result = FdPincodeMaster::where('pincode',$pincode)->WhereIn('flag', ['L','R'])->get();
                Log::info('Pincode Details'.json_encode($result));
                if(count($result) > 0) {
                    $response['message'] = 'success';
                    $response['result'] = $result;
                } else {
                    $response['message'] = 'failure';
                    $response['result'] = 'No Records Found';
                }
            }else{
                $response['message'] = 'failure';
                $response['result'] = 'No Records Found';
            }
        }catch(\Exception $e) {
            Log::info("Pincodeinfo ".$e->getMessage());
            $response['exception'] = $e->getMessage();
            $response['message'] = "Something went wrong, Please try again!";
            $response['errorCode'] = 404;
            $response['result'] = 'No Records Found';
        }
        return $response;  
    }
}

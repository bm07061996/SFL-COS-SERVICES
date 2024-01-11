<?php

namespace App\Services\V1;

use App\Services\BaseService;
use App\Utils\RestServiceTrait;
use Illuminate\Http\Request;
use App\Component\PostLogin\PostLoginFactory;


class PostLoginService extends BaseService
{    
    use RestServiceTrait;

    public function process(Request $request)
    {
        $result = [];
        
        if ($request["action"] == "getcomplaintproduct" || $request["action"] == "getclaimintimationinsurancetype" || $request["action"] == "getinsmasterdetails"  || $request["action"] == "getmyrewardsreferproduct" || strtolower($request["action"]) == "getleadapitoken") {
              $rules = [
                "type" => "required|alpha_dash",
                "action" => "required|alpha_dash"
            ];
        }else{
            $rules = [
                "type" => "required|alpha_dash",
                "action" => "required|alpha_dash",
                "data" => "required"
            ];
        }
        $validator = $this->validator($request->all(), $rules);
        if ($validator !== false) {
            return $validator;
        }
        $data = $request->all();
        if(empty($data['data']) === false && !is_array($data['data'])){
            $data['data'] = json_decode($data['data'],true);
        }
        $data['data'] = empty($data['data']) === false ? $data['data'] : [];

        $payment = PostLoginFactory::create($request->get('type'), "PostLoginProcess", $data);
        $function = "process";
       
        if ($payment) {
            if (method_exists($payment, $function)) {
                $payment->{$function}();
                   $result = empty($payment->response) === false ? $payment->response : array("message"=>"No data found");
                   \Log::info('$result'.json_encode($result));
                if(isset($result["Validation"])){
                    return $this->successResponse($result);
                }
            } else {
                $result['message'] = "Invalid request";
            }
            if(isset($result['message']) && $result['message'] == "Error"){
                   return $this->successResponse($result);
             }
             \Log::info('$result1'.json_encode($result));

            return $this->sendComponentResponse($result,$request);
        }else{
            $errorResult['message'] = "Not a valid request";
            return $this->successResponse($errorResult);
        }
       
    }


}
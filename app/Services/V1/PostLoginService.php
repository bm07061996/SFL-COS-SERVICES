<?php

namespace App\Services\V1;

use App\Component\PostLogin\PostLoginFactory;
use App\Services\BaseService;
use App\Utils\RestServiceTrait;
use Illuminate\Http\Request;

class PostLoginService extends BaseService
{    
    use RestServiceTrait;

    public function process(Request $request)
    {
        $result = [];
        $data = $request->all();
        if (empty($data['data']) === false && !is_array($data['data'])) {
            $data['data'] = json_decode($data['data'], true);
        }
		$data['data'] = $data['data'] ?? [];
        $postLogin = PostLoginFactory::create($data);
        $function = 'process';
        if ($postLogin) {
            if (method_exists($postLogin, $function)) {
                $postLogin->{$function}();
                $result = $postLogin->response ?? array('message' => 'No data found');
                if (!empty($result['Validation'])) {
                    return $this->validationResponse($result);
                }
            } else {
                $result['message'] = 'Invalid request';
            }
            if (!empty($result['message']) && $result['message'] == 'Error') {
                return $this->validationResponse($result);
            }
            return $this->successResponse($result, true);
        }else{
            $errorResult['message'] = 'Not a valid request';
            return $this->successResponse($errorResult, true);
        }
    }
}
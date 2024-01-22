<?php
namespace App\Services\V1;

use App\Services\BaseService;
use App\Utils\RestServiceTrait;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AuthService extends BaseService
{    
    use RestServiceTrait;
    
    public function login(Request $request)
    {
        $rules = [
			'data.mobile' => 'required|max:10|regex:/^[56789]\d{9}$/',
            'data.action' => 'required|in:otp,mpin',
            'data.code' => 'required|regex:/^[0-9]{4}(?:[0-9]{2})?$/',
        ];
        $validation = $this->validator($request->all(), $rules);
        if (empty($validation) === false) {
            return $this->validationResponse($validation);
        }
        $key = config('jwt.secret');
        if ($request['data']['action'] == 'otp') {
            $tokenId = base64_encode($request['data']['mobile'].$request['data']['code'].random_bytes(16));
        } else if ($request['data']['action'] == 'mpin') {
            $tokenId = base64_encode($request['data']['mobile'].$request['data']['code'].random_bytes(18));
        } else {
            $tokenId = base64_encode(random_bytes(32));
        }
        $ip = $request->ip();
        $expiration = Carbon::now()->addSeconds(3600)->timestamp;
        $token = [
            'jti' => $tokenId,
            'iss' => $ip,
            'iat' => Carbon::now()->timestamp,
            'exp' => $expiration,
            'data' => [
                'transKey' => $this->generateUUID()
            ]
        ];
        $jwt = JWT::encode($token, $key, 'HS256');

        return $this->successResponse(['token' => $jwt], true);
    }
}
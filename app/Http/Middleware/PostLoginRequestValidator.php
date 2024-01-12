<?php

namespace App\Http\Middleware;

use Closure;
use App\Utils\RestServiceTrait;

class PostLoginRequestValidator
{
    use RestServiceTrait;

    private $dataSkipActions = [ "getcomplaintproduct", "getclaimintimationinsurancetype", "getinsmasterdetails", "getinsmasterdetails", "getmyrewardsreferproduct"];

    public function handle($request, Closure $next, $guard = null)
    {

        if(in_array($request['actions'], $this->dataSkipActions) === false) {
            $rules = [
                "type"      => "required|string",
                "action"    => "required|string"
            ];
        } else {
            $rules = [
                "type"      => "required|string",
                "action"    => "required|string",
                "data"      => "required"
            ];
        }

        $validation = $this->validator($request->all(), $rules);

		if(empty($validation) === false){
            return $this->validationResponse($validation);
        }

        return $next($request);
    }
}

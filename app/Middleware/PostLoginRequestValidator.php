<?php

namespace App\Middleware;

use App\Utils\RestServiceTrait;
use Closure;

class PostLoginRequestValidator
{
    use RestServiceTrait;

    private $dataSkipActions = [ 'getcomplaintproduct', 'getclaimintimationinsurancetype', 'getinsmasterdetails', 'getinsmasterdetails', 'getmyrewardsreferproduct'];

    public function handle($request, Closure $next, $guard = null)
    {

        if (in_array($request['actions'], $this->dataSkipActions) === false) {
            $rules = [
                'type' => 'required|string',
                'action' => 'required|string'
            ];
        } else {
            $rules = [
                'type' => 'required|string',
                'action' => 'required|string',
                'data' => 'required'
            ];
        }

        $validation = $this->validator($request->all(), $rules);

		if (empty($validation) === false) {
            return $this->validationResponse($validation);
        }

        return $next($request);
    }
}

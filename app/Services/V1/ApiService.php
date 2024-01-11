<?php
namespace App\Services\V1;

use App\Services\BaseService;
use App\Utils\RestServiceTrait;

class ApiService extends BaseService
{    
    use RestServiceTrait;
    
    public function version()
    {
        return $this->successResponse([
            'api_name' => 'SFL Customer Portal Services',
            'version' => 'v1'
        ], true);
	}

}
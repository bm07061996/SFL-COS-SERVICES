<?php

namespace App\Component\PostLogin\PostLoginProcess\Modules\MyDeposit;

use App\Component\PostLogin\PostLoginProcess\PostLoginProcessAbstract;
use App\Component\PostLogin\PostLoginProcess\PostLoginProcessInterface;
use App\Repositories\Eloquent\FdPincodeRepository;
use App\Utils\HelperTrait;
use Illuminate\Support\Facades\Log;

class GetPincodeDetails extends PostLoginProcessAbstract implements PostLoginProcessInterface
{ 

    use HelperTrait;

    public $fdPincodeRepo;
    public $data;

	public function __construct($data)
	{
		$this->data = $data;
        $this->fdPincodeRepo =  app()->make(FdPincodeRepository::class);
	}

    public function process()
	{
        $data = $this->data['data'] ?? [];
        $this->response = $this->getPincodeDetails($data);
	    return $this;
	}

    public function getPincodeDetails($data) {
        try{
            $pincode    = $data['pincode'] ?? '';
            if ($pincode) {
			    $result = $this->fdPincodeRepo->searchByPincode($this->sanitizeEmptyVariable($data, 'pincode'));
                if(count($result) > 0) {
                    $response['message'] = 'success';
                    $response['result'] = $result;
                } else {
                    $response['message'] = 'failure';
                    $response['result'] = 'No Records Found';
                }
            } else {
                $response['message'] = 'failure';
                $response['result'] = 'No Records Found';
            }
        }catch(\Exception $e) {
            Log::info('Pincodeinfo '.$e->getMessage());
            $response['exception'] = $e->getMessage();
            $response['message'] = 'Something went wrong, Please try again!';
            $response['errorCode'] = 404;
            $response['result'] = 'No Records Found';
        }
        return $response;  
    }
}
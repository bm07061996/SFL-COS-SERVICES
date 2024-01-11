<?php

namespace App\Component;
use App\Component\Swift_SmtpTransport;

trait ComponentHelperTraits
{
	public function pager($model)
    {
        $this->response['pagination']['total'] = $model->total();
        $this->response['pagination']['currentPage'] = $model->currentPage();
        $this->response['pagination']['lastPage'] = $model->lastPage();
        $this->response['pagination']['perPage'] = $model->perPage();
    }

    public function getCategory()
	{
		$communityCategory = \App\Entities\CommunityCategory::where("is_active", 1);

		if (empty($this->data['communityId']) === false) {
			$id = empty($this->response['community'][0]['category'][0]['id']) === false ? $this->response['community'][0]['category'][0]['id'] : null;
			$communityCategory = $communityCategory->where("_id", (string) $id);
		} elseif (empty($this->data['categoryId']) === false) {
			$communityCategory = $communityCategory->where("_id", $this->data['categoryId']);
		} else {
			$communityCategory = $communityCategory->where("slug", $this->data['type']);
		}

		$communityCategory = $communityCategory->get();

		$this->response['category'] = !$communityCategory->isEmpty() ? app(\App\Transformers\CommunityCategoryTransformer::class)->transformCollection($communityCategory) : [];
	}

	public function getPopularCommunity($type = "")
	{
		$condition['is_active'] = 1;
		$condition['type'] = $type ? $type : $this->data['type'];

		if ($type == "article" || $type == "forum" && empty($this->data['breadcrumbs'][0]) === false) {
			$text = $this->data['breadcrumbs'][0];
			$searchText = explode("-", $text['url']);
			if (empty($searchText[0]) === false)
				$condition['url'] = new \MongoDB\BSON\Regex($searchText[0], "i");
			$community = \App\Entities\Community::raw(function($collection) use ($condition){
				return $collection->aggregate(
					[
						[
							"\$match" => $condition
						],
						[
							"\$sample" => [
								"size" => 21
							]
						]
					]
				);
			});
		} else {
			$community = \App\Entities\Community::raw(function($collection) use ($condition){
				return $collection->aggregate(
					[
						[
							"\$match" => $condition
						],
						[
							"\$sample" => [
								"size" => 12
							]
						]
					]
				);
			});
		}

		return !$community->isEmpty() ? app(\App\Transformers\CommunityUrlTransformer::class)->transformCollection($community) : [];
	}

	public function getObjectId($id)
    {
        return new \MongoDB\BSON\ObjectID($id);
    }

    public function modelClone($model)
    {
    	$clone = new \Illuminate\Database\Eloquent\Builder(clone $model->getQuery());
    	$clone->setModel($model->getModel());

    	return $clone;
    }

	public function emailTrigger($emailParams)
    {
    	if(empty($emailParams['keyCheck']) === false && $emailParams['keyCheck'] == "no"){
			$msg = $this->smtp($emailParams);
		}else{
			if(empty($emailParams['from'])===true || empty($emailParams['fromName'])===true || empty($emailParams['subject'])===true || empty($emailParams['template'])===true || empty($emailParams['to'])===true ) {
				return false;
			}
			$msg = $this->smtp($emailParams);
		}
        return $msg;
    }

    public function smtp($data)
    {
        app()->configure('mail');
        try{
        	$bodyContent = '';
        	if( empty($data['template']) === false) {
        		foreach($data['template'] as $key => $value) {
        			$bodyContent .= '<tr><td style="padding:5px;"><b>'.ucfirst($key).'</b></td><td style="padding:5px;">:</td><td style="padding:5px;">'.$value.'</td><tr>';
        		}
        	}
        	$body = '<html><thead><style>tr{pading: 5px;}</style></thead><body><table><tbody>'.$bodyContent.'</tbody></table></body></html>';

        	// Create the Transport
			$transport = (new \Swift_SmtpTransport(config('mail.host'), config('mail.port')))
			  ->setUsername(config('mail.username'))
			  ->setPassword(config('mail.password'));

			// Create the Mailer using your created Transport
			$mailer = new \Swift_Mailer($transport);

			// Create a message
			$message = (new \Swift_Message($data['subject']))
			  // ->setFrom([$data['from'] => $data['fromName']])
			  ->setFrom([config('mail.fromAddress') => config('mail.fromAddressName')])
			  ->setTo($data['to'])
			  ->setBody($body,'text/html');

			if( empty( $data['cc'] ) === false ) {
            	$message->setCc($data['cc']);
            }

            if( empty( $data['bcc'] ) === false ) {
            	$message->setBcc($data['bcc']);
            }

			return $mailer->send($message);
        }
        catch(\Exception $e){
            throw new \Exception($e->getMessage());            
        }

        return true;    
    }

	public function encryptAES($data,$key,$iv,$cipher)
    {
        try{
            return openssl_encrypt($data, $cipher, $key, $options=0, $iv);
        }catch(\Exception $e){
            \log::info('Exception'.$e->getMessage());    
        }
    } 
    public function decryptAES($data,$key,$iv,$cipher)
    {
        try{
            return openssl_decrypt($data, $cipher, $key, $options=0, $iv);
        }catch(\Exception $e){
            \log::info('Exception'.$e->getMessage());    
        } 
    }

	public function unifiedPayEMIEncryptData($reqData)
	{
		try {

			$key = config('api.unifiedPayEMIEncryptKey');
			$iv = config('api.unifiedPayEMIEncryptIv');
			$cipher = config('api.unifiedPayEMIAesCipher');
			//$reqData = json_encode($reqData);
			$encrypted = openssl_encrypt($reqData, $cipher, $key, $options = 0, $iv);
			return $encrypted;
		} catch (\Exception $e) {
		\log::info('Exception' . $e->getMessage());
		}     
	}

	public function unifiedPayEMIDecryptData($resdata)
	{
		try {
			//$key = '9513574562580759';
			//$iv = '7648513279456184';
			$key = config('api.unifiedPayEMIDecryptKey');
			$iv = config('api.unifiedPayEMIDecryptIv');
			$cipher = config('api.unifiedPayEMIAesCipher');
			$decrypted = openssl_decrypt($resdata, $cipher, $key, $options = 0, $iv);
			return $decrypted = json_decode($decrypted ,true);
		} catch (\Exception $e) {
			\log::info('Exception' . $e->getMessage());
		}
	}
}
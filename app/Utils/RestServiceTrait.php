<?php

namespace App\Utils;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

trait RestServiceTrait
{

	public function successResponse($data, $dataKeyRequired = false)
	{
		$response = [
			'status' => 200,
			'message' => 'success',
			'success' => true
		];
		$response['data'] = $data;
		if ($dataKeyRequired === true) {
			$response['data'] = $data;
		}
		else {
			$response['data'] = $data['data'] ?? [];
		}
		return response()->json($response, $response['status']);
	}

	public function validationResponse($errors)
	{
		$response = [
			'status' => 422,
			'message' => 'Unprocessable Entity',
			'data' => [],
			'success' => false,
			'errors' => $errors['errors'] ?? []
		];
		return response()->json($response, $response['status']);
	}

	public function validator(array $request, array $rules, $messages = [], $jsonResponse = false)
    {
        $validator = \Validator::make($request, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $messagesFormat = [];
            $messagesFormat['errors'] = [];
            foreach ($messages->toArray() as $key => $message) {
            	if (array_key_exists($key, $messagesFormat['errors']) == false) {
            		$messagesFormat['errors'][$key] = [];
            	}

            	array_push($messagesFormat['errors'][$key], $message[0]);
            }

            return $jsonResponse ? $this->validationResponse($messagesFormat) : $messagesFormat;
        }

        return false;
    }
}
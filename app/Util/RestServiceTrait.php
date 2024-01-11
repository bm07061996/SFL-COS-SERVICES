<?php
namespace App\Util;

trait RestServiceTrait
{
	protected function showResponse($data, $code = 200)
    {
        $response = [
            'code' => $code,
            'status' => true,
            'data' => $data
        ];

        return response()->json($response, $response['code']);
    }

    protected function showErrorResponse($data)
    {
        $response = [
            'code' => 500,
            'status' => false,
            'data' => $data
        ];

        return response()->json($response, $response['code']);
    }

    protected function showValidationResponse($data)
    {
        $response = [
            'code' => 422,
            'status' => false,
            'data' => $data
        ];

        return response()->json($response, $response['code']);
    }

    protected function listResponse($data)
    {
        $response = [
            'code' => 200,
            'status' => true,
            'data' => $data
        ];

        return response()->json($response, $response['code']);
    }
    
    protected function showOtherResponse($data,$code=500)
    {
        $response = [
            'code' => $code,
            'status' => false,
            'data' => $data
        ];
        return response()->json($response, $response['code']);
    }
}

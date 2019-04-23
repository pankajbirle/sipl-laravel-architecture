<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

use OpenApi as OA;

/**
 * @OA\Server(url="http://127.0.0.1:8000/")
 * @OA\Info(title="Laravel API",
 *     version="0.1" ,
 *     description="An example resource",
 *     contact={
 *              "email": "support@example.com"
 *          }
 *     )
 */

class ApiBaseController extends Controller
{

    public function sendSuccessResponse($result = [], $code = 200, $token = '')
    {

        if(is_array($result) && count($result) == 0){
            $result = (object)$result;
        }
        $response = [
            'success'   => $result
        ];
        if($token && $token != ''){
            return response()->json($response, $code)->header('token', $token);
        }
        return response()->json($response, $code);
    }

    /*
     * function for send failure response
     */
    public function sendFailureResponse($message = 'Something went wrong.', $code = 422)
    {
        $response = [
            'error'   => $message,
        ];

        return response($response, $code);
    }
}

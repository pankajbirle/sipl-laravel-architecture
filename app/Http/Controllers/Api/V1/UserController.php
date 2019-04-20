<?php

namespace App\Http\Controllers\Api\V1;



use App\Http\Controllers\ApiBaseController;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class UserController extends ApiBaseController
{
    /**
     * @OA\Get(
     *   path="/api/v1/users",
     *   tags={"User"},
     *   operationId="index",
     *   summary="list users",
     *   @OA\Response(response=200, description="OK", ),
     *   @OA\Response(response=400, description="Bad request, Invalid param pass in request", ),
     *   @OA\Response(response=401, description="Unauthorised access.", ),
     *   @OA\Response(response=404, description="Not found.", ),
     *   @OA\Response(response=422, description="not acceptable, validation related errors"),
     *   @OA\Response(response=429, description="Too many request."),
     *   @OA\Response(response=500, description="internal server error"),
     *   @OA\Response(response=503, description="Service Unavailable.")
     * )
     */
    public function index()
    {
        try {
            $data['users'] = User::all();
            $data['message'] = 'User listing success';
            return $this->sendSuccessResponse($data);

        } catch (\Exception $e) {
            return $this->sendFailureResponse();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(){
        try {
            if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
                $user = Auth::user();
                $data['token'] =  $user->createToken('MyApp')->accessToken;
                $data['permissions'] = $user->getUserPermissionsViaRoles($user);
                return $this->sendSuccessResponse($data);
            }
            else{
                return $this->sendFailureResponse('Unauthorised', 401);
            }
        } catch (\Exception $e) {
            return $this->sendFailureResponse();
        }

    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'c_password' => 'required|same:password',
            ]);
            if ($validator->fails()) {
                return $this->sendFailureResponse($validator->errors(), 401);
            }
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $user->assignRole('user');
            $data['token'] =  $user->createToken('MyApp')->accessToken;
            $data['permissions'] = $user->getUserPermissionsViaRoles($user);
            $data['name'] =  $user->name;
            return $this->sendSuccessResponse($data, 201);
        } catch (\Exception $e) {
            return $this->sendFailureResponse();
        }

    }

    public function details()
    {
        try {
            $user = Auth::user();
            return $this->sendSuccessResponse($user);
        } catch (\Exception $e) {
            return $this->sendFailureResponse();
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * @OA\Get(
     *   path="/api/v1/user/",
     *   tags={"User"},
     *   operationId="show",
     *   summary="fetch user detail",
     *   @OA\Parameter(name="sync_data", in="query", required=true, @OA\Schema(
     *             @OA\Property(property="pos_unique_id")
     *         )),
     *   @OA\Response(response=200, description="OK", ),
     *   @OA\Response(response=400, description="Bad request, Invalid param pass in request", ),
     *   @OA\Response(response=401, description="Unauthorised access.", ),
     *   @OA\Response(response=404, description="Not found.", ),
     *   @OA\Response(response=422, description="not acceptable, validation related errors"),
     *   @OA\Response(response=429, description="Too many request."),
     *   @OA\Response(response=500, description="internal server error"),
     *   @OA\Response(response=503, description="Service Unavailable.")
     * )
     */
    public function show($id)
    {
        try {
            $user = User::findOrfail($id);
            return $this->sendSuccessResponse($user);
        } catch (ModelNotFoundException $e) {
            return $this->sendFailureResponse('User not found');
        } catch (\Exception $e) {
            return $this->sendFailureResponse();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Api\V1;



use App\Http\Controllers\ApiBaseController;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
     *   @OA\Response(response=401, description="Unauthenticated access.", ),
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
     * function for user login from API
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function login(){
        try {
            if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
                $user = Auth::user();
                $token =  $user->createToken('MyApp')->accessToken;
                $data['user'] = $user;
                $data['user']['roles'] = User::getUserRoles($user->id);
                $data['user']['permissions'] = User::getUserPermissionsViaRoles($user->id);
                return $this->sendSuccessResponse($data, 200, $token);
            }
            else{
                return $this->sendFailureResponse('Unauthenticated', 401);
            }
        } catch (\Exception $e) {
            return $this->sendFailureResponse();
        }

    }

    /**
     * function for register user from api
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'c_password' => 'required|same:password',
            ]);
            if ($validator->fails()) {
                return $this->sendFailureResponse($validator->errors(), 422);
            }
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $user->assignRole('user');
            $token =  $user->createToken('MyApp')->accessToken;
            $data['user'] = User::findOrfail($user->id);
            $data['user']['roles'] = User::getUserRoles($user->id);
            $data['user']['permissions'] = User::getUserPermissionsViaRoles($user->id);
            return $this->sendSuccessResponse($data, 201, $token);
        } catch (\Exception $e) {
            return $this->sendFailureResponse($e->getMessage());
        }

    }

    public function details()
    {
        try {
            $data['user'] = $user = Auth::user();
            $data['user']['roles'] = User::getUserRoles($user->id);
            $data['user']['permissions'] = User::getUserPermissionsViaRoles($user->id);
            return $this->sendSuccessResponse($data);
        } catch (\Exception $e) {
            return $this->sendFailureResponse($e->getMessage());
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
     *   @OA\Response(response=401, description="Unauthenticated access.", ),
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
            $data['user'] = $user = User::findOrfail($id);
            $data['user']['roles'] = User::getUserRoles($user->id);
            $data['user']['permissions'] = User::getUserPermissionsViaRoles($user->id);
            return $this->sendSuccessResponse($data);
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

<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Facades\Auditor;
use Illuminate\Support\Facades\Validator;

class PostController extends ApiBaseController
{
    public function index()
    {
        //updated
        $post = Post::find(2);
        $post->post = 'test';
        $post->save();
        echo $post->id.'</br>';

        //created
        $post = new Post();
        $post->post = 'My post';
        $post->user_id = 2;
        $post->save();

        //Delete
        $post = Post::find(2);
        $post->delete();


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'post' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendFailureResponse($validator->errors(), 422);
            }
            $post = new Post();
            $post->post = $request->post;
            $post->user_id = Auth::id();
            $post->save();
            return $this->sendSuccessResponse('Post has been updated successfully-'.$post->id, 200);

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
        try {
            $validator = Validator::make($request->all(), [
                'post' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendFailureResponse($validator->errors(), 422);
            }
            $post = Post::find($id);
            $post->post = $request->post;
            $post->save();
            return $this->sendSuccessResponse('Post has been updated successfully'.$post->id, 200);

        } catch (\Exception $e) {
            return $this->sendFailureResponse();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $post = Post::find($id);
            $post->delete();
            return $this->sendSuccessResponse('Post has been deleted successfully', 204);

        } catch (\Exception $e) {
            return $this->sendFailureResponse();
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         //Role::create(['name' => 'admin']);
        /*$role = Role::findByName('super-admin');
        $role->givePermissionTo('add_user');
        $role->givePermissionTo('edit_user');
        $role->givePermissionTo('delete_user');
        $role->givePermissionTo('list_user');
        $role->givePermissionTo('view_user');

        $role = Role::findByName('admin');
        $role->givePermissionTo('add_user');
        $role->givePermissionTo('edit_user');
        $role->givePermissionTo('delete_user');
        $role->givePermissionTo('list_user');
        $role->givePermissionTo('view_user');

        $role = Role::findByName('manager');
        $role->givePermissionTo('add_user');
        $role->givePermissionTo('edit_user');

        $role->givePermissionTo('list_user');
        $role->givePermissionTo('view_user');

        $role = Role::findByName('user');
        //$role->givePermissionTo('add_user');
        $role->givePermissionTo('edit_user');
        //$role->givePermissionTo('delete_user');
        //$role->givePermissionTo('list_user');
        $role->givePermissionTo('view_user');*/
        $user = User::find(4);
        $user->assignRole('super-admin');

        $user = User::find(3);
        $user->assignRole('admin');

        $user = User::find(2);
        $user->assignRole('manager');

        $user = User::find(1);
        $user->assignRole('user');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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

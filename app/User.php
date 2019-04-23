<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use OwenIt\Auditing\Contracts\Auditable;

use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Auditable
{
    use Notifiable, HasApiTokens, HasRoles;

    use \OwenIt\Auditing\Auditable;
    protected $auditStrict = true;
    protected $auditInclude = [
        'name', 'email', 'password',
    ];

    protected $auditEvents = [
        'deleted',
        'restored',
        'created',
        'saved',
        'deleted',
        'updated',
        'update',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * for get user permissions via role
     * @param $user
     * @return array
     */
    public static function getUserPermissionsViaRoles($id){
        $user = User::find($id);
        $permissions = $user->getPermissionsViaRoles();
        $permissionList = [];
        if($permissions && count($permissions) > 0) {
            foreach ($permissions as $permission){
                array_push($permissionList, $permission->name);
            }
        }
        return $permissionList;
    }


    /**
     * get user all roles
     * @param $user
     * @return array
     */
    public static function getUserRoles($id){
        $roles = self::findOrfail($id)->getRoleNames();
        return $roles;
    }
}

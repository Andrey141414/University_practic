<?php


namespace App\Service;

use App\Models\Permission;
use App\Models\Role;


class RolePolicyService
{
    public static function getUsreRoles($id_user)
    {
        $roles = [];
        $permissions = Permission::where('id_user', $id_user)->get();
        if ($permissions) {
            foreach ($permissions as $permission) {
                $roles[] = Role::find($permission->id_role)->raw_value;
            }
        } else {
            return $roles[] = 'user';
        }
        return $roles;
    }
}

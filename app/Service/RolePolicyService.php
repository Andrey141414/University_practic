<?php


namespace App\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\postModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\postFilterRequest;
use App\Models\AddressModel;
use App\Models\CategoryModel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\favoritePost;
use App\Models\postStatus;
use App\Models\reviewModel;


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

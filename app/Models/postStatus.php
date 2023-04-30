<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class postStatus extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "post_status";

    
    // protected $admin_updatable = [
    //     'active',
    //     'banned',
    //     'pending',
    //     'rejected',
    //     'reserved',
    // ];

    public static function getStatusName($id)
    {
        return postStatus::find($id)->raw_value;
    }
    public static function getStatusid($name)
    {
        return postStatus::where('raw_value',$name)->first()->id;
    }

}


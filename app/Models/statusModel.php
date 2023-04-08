<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class statusModel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "post_status";

    
    public static function getStatusName($id)
    {
        return statusModel::find($id)->raw_value;
    }
    public static function getStatusid($name)
    {
        return statusModel::where('raw_value',$name)->first()->id;
    }

}


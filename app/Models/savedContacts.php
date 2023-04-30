<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class savedContacts extends Model
{
    public $timestamps = false;
    protected $table = "saved_contacts";

    protected $fillable = [];
    protected $guarded = [];

    static function saveContacts($id_user,$id_post){
        savedContacts::create([
            'id_user' => $id_user,
            'id_post' => $id_post,
        ]);
    }

    static function isContactsSaved($id_user,$id_post){
        
        
        if(savedContacts::where('id_user',$id_user)->where('id_post' ,$id_post)->first() == null){
            return false; 
        }
        return true;
    }
}

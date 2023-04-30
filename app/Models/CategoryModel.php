<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "category";
    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'is_active',
        'icon',
    ];
    protected $guarded = [];
    
    
    public static function getCategoryModel($id_category)
    {
        return  self::find($id_category);
    }
}


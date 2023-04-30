<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityModel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "city";
    
    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'is_active',
    ];
    protected $guarded = [];
    public static function getCityModel($id_city)
    {
        return  self::find($id_city);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reservation extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "reservations";

    protected $fillable = [];
    protected $guarded = [];
}

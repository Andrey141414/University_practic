<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reviewModel extends Model
{

    protected $casts = [
        'score' => 'integer',
    ];

    use HasFactory;
    protected $table = "review";
    public $timestamps = false;
    protected $guarded = [];
}

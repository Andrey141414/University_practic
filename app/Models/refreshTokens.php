<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class refreshTokens extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "oauth_refresh_tokens";
}

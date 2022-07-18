<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class accessTokens extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "oauth_access_tokens";
}

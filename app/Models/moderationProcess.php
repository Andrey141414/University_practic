<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class moderationProcess extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "moderation_process";
    protected $fillable = [];
    protected $guarded = [];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class postModel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "post";

    protected $casts = [
        'is_active' => 'boolean',
        'show_email' => 'boolean',
    ];

    protected $fillable = [];
    protected $guarded = [];

    public $updatable = [
        'title',
        'description',
        'image_set',
        'id_category',
        'address',
        'id_city',
        'show_email',
    ];

    public function checkUpdatable($prop)
    {
        if (in_array($prop, $this->updatable))
            return true;
        else
            return false;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 *      @OA\Property(property="role", type="string", readOnly="true", description="User role"),
 * )
 *
 *
 */
class AddressModel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "favorite_address";

    public function getAddress($id)
    {
        return $this->where('id',$id)->first();
    }    

    protected $guarded = [];
}

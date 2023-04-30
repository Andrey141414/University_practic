<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\reservation;

class reservationStatus extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "reservation_status";


    //Карта статусов, показывает из какого статуса в какие можнет перейти бронь
    protected $statusMap = [
        'order' =>  ['cancel','confirm_sent'],
        'confirm_sent' => ['cancel','completed']
    ];

    public function checkStatusMove($id_res, $newStatus)
    {
        $res = reservation::find($id_res);
        if (!$res) {
            return false;
        }
        $current_status = $res->status;
        foreach ($this->statusMap as $previous => $moveStatus) {
            if ($previous == $current_status && in_array($newStatus, $moveStatus)) {
                return $res;
            }
        }

        return false;
    }
}

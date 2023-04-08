<?php

namespace App\Service;

use Illuminate\Support\ServiceProvider;
use App\Models\AddressModel;
use App\Models\CityModel;
class AddressService
{
    public static function getShortAddressModel($id_city)
    {
        return  CityModel::find($id_city);
       
    }
}

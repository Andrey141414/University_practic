<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddressModel;
use Illuminate\Support\Facades\Storage;
use Nette\Utils\Json;

class AddressController extends Controller
{
    public function addNewAddress(Request $request)
    {
        $id_user = auth('api')->user()->id;
        $address = new AddressModel();

        if($address->where('id_user',$id_user)->count()>=5)
        {
            return response()->json([
                "message" => "More 5 addresses",
            ], 507);
        }
        $address->title = $request->input('title');
        $address->id_user=$id_user;
        $address->save();
        return response()->json($address::all(),200);
    }

    public function deleteAddress(Request $request){

        //$id_user = auth('api')->user()->id;
        //$title = $request->get('title');
        $id_address = $request->get('id_address');
        $address = (new AddressModel())->where('id',$id_address)->first();


        
        if($address==null)
        {
            return response()->json([
                "message" => "There is no so address",
            ], 204); 
        }
        $address->delete();

        return 200;
    }

    
    public function changeAddress(Request $request)
    {
        
        $id_address = $request->input('id_address');
        $address = (new AddressModel())->where('id',$id_address)->first();
        
        if($address==null)
        {
            return response()->json([
                "message" => "There is no so address",
            ], 204); 
        }

        $address->title = $request->input('title');
        $address->save();
        return response()->json($address::all(),200);
    }
}

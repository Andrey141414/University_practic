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
        response()->json(["message"=>"Address was saved"],200);
    }

    public function deleteAddress(Request $request){

        $id_user = auth('api')->user()->id;
        
        $id_address = $request->get('id_address');
        $address = (new AddressModel())->where('id',$id_address)->first();


        
        if($address==null)
        {
            return response()->json([
                "message" => "There is no so address",
            ], 204); 
        }
        if($address->id_user != $id_user)
        {
            return response()->json([
                "message" => "There is no so address for you",
            ], 204);
        }
        $address->delete();

        return response()->json(["message"=>"Address was deleted"],200);
    }

    
    public function changeAddress(Request $request)
    {
        $id_user = auth('api')->user()->id;
        $id_address = $request->input('id_address');
        $address = (new AddressModel())->where('id',$id_address)->first();
        
        if($address==null)
        {
            return response()->json([
                "message" => "There is no so address",
            ], 204); 
        }

        if($address->id_user != $id_user)
        {
            return response()->json([
                "message" => "There is no so address for you",
            ], 204);
        }
        $address->title = $request->input('title');
        $address->save();
        return response()->json(["message"=>"Address was saved"],200);
    }
}

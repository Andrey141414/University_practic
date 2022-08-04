<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddressModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Nette\Utils\Json;

class AddressController extends Controller
{
    public function addNewAddress(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'id_city' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }

        $id_user = auth('api')->user()->id;
        $address = new AddressModel();
        $title = $request->input('title');
        $id_city = $request->input('id_city');
        if((new AddressModel())->where('title',$title)->where('id_user',$id_user)->first()!=null)
        {
            return response()->json([
                "message" => "You have this address allready",
            ], 400);
        }
        
        if($address->where('id_user',$id_user)->count()>=5)
        {
            return response()->json([
                "message" => "More 5 addresses",
            ], 507);
        
        $address->id_city = $id_city;
        $address->title = $title;
        $address->id_user=$id_user;
        $address->save();
        $add = $address->where('id_user',$id_user)->where('title',$title)->first();
        return [response()->json([
        "id_address"=>$add->id,
    ],200),];
    }
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
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'id_city' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }


        $id_user = auth('api')->user()->id;
        $id_address = $request->input('id_address');
        $address = (new AddressModel())->where('id',$id_address)->first();
        $id_city = $request->input('id_city');

       

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
        $address->id_city = $id_city;
        $address->id_user=$id_user;
        
        $address->save();
        return response()->json(["message"=>"Address was saved"],200);
    }
}

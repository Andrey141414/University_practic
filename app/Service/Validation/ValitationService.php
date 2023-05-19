<?php


namespace App\Service\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\postModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\postFilterRequest;
use App\Models\AddressModel;
use App\Models\CategoryModel;
use App\Models\CityModel;
use App\Models\favoritePost;
use App\Models\postStatus;
use App\Models\reviewModel;


class ValitationService
{
    protected $attributes;
    protected $rules;
    protected $messages ;
    protected $data;
    public $errors;

    

    public function set($data,$rules){
        $this->data = $data;
        $this->rules = $rules;
        $in = '/home/in-good-hands/web/in-good-hands.dev.mind4.me/application/lang/ru/validation.php';
        $this->messages = include $in;
    }

    public function validate(){
        $validator = Validator::make($this->data, $this->rules, $this->messages);
        if($validator -> fails())
        {
            $this->errors = $validator->errors();
            return null;
        }
        return true;
    }

    public function ruturnError(){
        //return $this->errors;
        return $this->errors->first();
    }
}

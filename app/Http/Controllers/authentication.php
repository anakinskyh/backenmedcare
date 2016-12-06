<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class authentication extends Controller
{
    //return jwt or error
    public function signIn(Request $request){

        // for user ssn for staff email
        $input = $request->all();

        if(array_key_exists('ssn',$input)){

            //as patient
            $validator = Validator::make($request->all(),[
                'ssn'=>'required|exists:Patient',
                'password'=>'required',
            ]);

            if($validator->fails())
                return $validator->errors()->all();

            $userdata = DB::select(DB::raw('
                SELECT *
                FROM Patient
                WHERE ssn = :ssn
                AND password = :password
            '),$input);

            //return jwt

        }else if(array_key_exists('email',$input)){

            //as staff

        }


    }
    public function auth(Request $request){
        $jwt = $request->only('jwt');

        
    }

    public function forgotPWD(Request $request){

    }
}
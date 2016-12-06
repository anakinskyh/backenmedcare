<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class userMgt extends Controller
{
    //
    /*
    public function submitAddUser(Request $request){
        $validator = Validator::make($request->all(),[
            'firstname'=>'required|Between:3,64',
            'lastname'=>'required|Between:3,64',
            'email'=>'required|Email|Between:3,64|Unique:Staff,email',
            'password'=>'required|min:8',
            'gender'=>'required',
            'tel'=>'required',
            'role'=>'required',
            'department_id'=>'required|exists:Department,id'
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        return response()->json(['message' => 'Request completed']);

    }//success
*/
    public function confirmAddUser(Request $request){
        //return 'what';
        //return response()->json($request->all());

        $validator = Validator::make($request->all(),[
            'firstname'=>'required|Between:3,64',
            'lastname'=>'required|Between:3,64',
            'email'=>'required|Email|Between:3,64|Unique:Staff,email',
            'password'=>'required|min:8',
            'tel'=>'required',
            'role'=>'required',
            'department_id'=>'required|exists:Department,id'
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $input = $request->all();
        //$input['password'] = bcrypt($input['password']);
        DB::table('Staff')
            ->insert($input);


        return response()->json(['status'=>'done']);
    }//success
    public function showScheduleForMed(Request $request){
        $validator = Validator::make($request->all(),[
            'start'=>'required',
            'end'=>'required',
            'doctor_id'=>'required|exists:staff,id',
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $input = $request->all();
        $result = DB::select( DB::raw('
          SELECT start,syntom,id,firstname,lastname
          FROM Appointment,Patient 
          WHERE start>=:start and start<=:end'),
            $input
        );

        return $result;
    }//success

    public function confirmAddPatient(Request $request){
        $validator = Validator::make($request->all(),[
            'firstname'=>'required|Between:3,64',
            'lastname'=>'required|Between:3,64',
            'email'=>'required|Email|Between:3,64|Unique:Patient,email',
            'password'=>'required|min:8',
            'tel'=>'required',
            'ssn'=>'required|Between:13,13'
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $input = $request->all();
        //$input['password'] = bcrypt($input['password']);
        DB::table('Patient')
            ->insert($input);

        $patient = DB::table('Patient')
            ->where('email','=',$input['email'])
            ->select('*')
            ->get();

        $pid = $patient[0]->id;

        app('App\Http\Controllers\emailMgt')->signupnoti($pid);
        app('App\Http\Controllers\smsMgt')->signupnoti($pid);

        return response()->json(['status'=>'done']);
    }//success

    public function patientEasySignin(Request $request){
        $validator = Validator::make($request->all(),[
            'ssn'=>'required',
            'password'=>'required'
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $input = $request->all();
        //$input['password']=bcrypt($input['password']);
        $result = DB::select(DB::raw('
            SELECT *
            FROM Patient
            WHERE ssn = :ssn
            AND password = :password
        '),$input);

        if(sizeof($result) != 0)
            $result['status']='done';
        else $result['status']='bad';

        return response()->json($result);
    }

    public function staffEasySignin(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=>'required',
            'password'=>'required'
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $input = $request->all();
        //$input['password']=bcrypt($input['password']);
        $result = DB::select(DB::raw('
            SELECT *
            FROM Staff
            WHERE email = :email
            AND password = :password
        '),$input);

        if(sizeof($result) != 0)
            $result['status']='done';
        else $result['status']='bad';

        return response()->json($result);
    }

    public function getPatientInfo(Request $request){
        $validator = Validator::make($request->all(),[
            'patient_id'=>'required|exists:Patient,id',
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $patient_id = $request->all()['patient_id'];
        $patient = DB::table('Patient')
            ->where('id','=',$patient_id)
            ->get();

        return response()->json($patient);
    }

    public function editPatientInfo(Request $request){
        $validator = Validator::make($request->all(),[
            'patient_id'=>'required|exists:Patient,id',
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $input = $request->all();
        $patient_id = $input['patient_id'];
        unset($input['patient_id']);
        DB::table('Patient')
            ->where('id','=',$patient_id)
            ->update($input);
        
        return response()->json(['status'=>'done']);
    }

    public function changePatientPassword(){
        $validator = Validator::make($request->all(),[
            'ssn'=>'required|exists:Patient,ssn',
            'newpassword'=>'required|min:8',
        ]);

        if($validator->fails())
            return $validator->errors()->all();
    }

    public function resetPatientPWD(Request $request){
        $validator = Validator::make($request->all(),[
            'ssn'=>'required|exists:Patient,ssn',
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $newpwd = userMgt::generateRandomString();

        $input = $request->all();
        $update = ['password'=>$newpwd];
        DB::table('Patient')
            ->where('Patient.ssn','=',$input['ssn'])
            ->update($update);

        $user = DB::table('Patient')->where('ssn','=',$input['ssn'])->get();

        $userid = $user[0]->id;

        app('App\Http\Controllers\emailMgt')->resetpassword($userid);
        app('App\Http\Controllers\smsMgt')->resetpassword($userid);

        return response()->json(['status'=>'done']);
    }

    public function resetStaffPWD(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=>'required|exists:Staff,email',
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $newpwd = userMgt::generateRandomString();

        $input = $request->all();
        $update = ['password'=>$newpwd];
        DB::table('Staff')
            ->where('Staff.email','=',$input['email'])
            ->update($update);

        $user = DB::table('Staff')->where('email','=',$input['email'])->get();

        $userid = $user[0]->id;

        app('App\Http\Controllers\emailMgt')->resetstaffpassword($userid);
        app('App\Http\Controllers\smsMgt')->resetstaffpassword($userid);

        return response()->json(['status'=>'done']);
    }

    function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
}

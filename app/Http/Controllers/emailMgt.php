<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class emailMgt extends Controller
{
    public function demo(Request $request){
        $input = $request->all();

        $doctor_id = $input['doctor_id'];
        $patient_id = $input['patient_id'];
        $datetime = $input['datetime'];

        $this->sendtopatient($doctor_id,$patient_id,$datetime);
    }
    //
    public function sendtopatient($doctor_id,$patient_id,$date,$time){


        $patient = DB::table('Patient')
            ->where('id','=',$patient_id)
            ->select('*')
            ->get();

        $email = $patient[0]->email;

        $doctor = DB::table('Staff')
            ->where('id','=',$doctor_id)
            ->select('*')
            ->get();

        $doctor_name = $doctor[0]->firstname.' '.$doctor[0]->lastname;

        $department = DB::table('Department')
            ->where('id','=',$doctor[0]->department_id)
            ->select('*')
            ->get();

        $department_name = $department[0]->name;

        $data = array(
            'email'=>$email,
        );
        Mail::send('emails.apptpatient', ['department_name'=>$department_name,'doctor_name'=>$doctor_name
            ,'data'=>$data,'date'=>$date,'time'=>$time], function ($message)
            use ($data)
        {
            $message->to($data['email']);
        });

        return response()->json(['status' => 'send mail' ]);
    }

    public function resetpassword($patient_id){

        $patient = DB::table('Patient')
            ->where('id','=',$patient_id)
            ->select('*')->get();

        $data = array(
            'email'=>$patient[0]->email,
        );

        $newpassword = $patient[0]->password;
        $name =$patient[0]->firstname.' '.$patient[0]->lastname;

        Mail::send('emails.resetpassword', 
        ['name'=>$name,'newpassword'=>$newpassword,'data'=>$data], function ($message)
            use ($data)
        {
            $message->to($data['email']);
        });

        return response()->json('status : send email');
    }

    public function resetstaffpassword($patient_id){

        $patient = DB::table('Staff')
            ->where('id','=',$patient_id)
            ->select('*')->get();

        $data = array(
            'email'=>$patient[0]->email,
        );

        $newpassword = $patient[0]->password;
        $name =$patient[0]->firstname.' '.$patient[0]->lastname;

        Mail::send('emails.resetpassword', 
        ['name'=>$name,'newpassword'=>$newpassword,'data'=>$data], function ($message)
            use ($data)
        {
            $message->to($data['email']);
        });

        return response()->json('status : send email');
    }

    public function signupnoti($patient_id){

        $patient = DB::table('Patient')
            ->where('id','=',$patient_id)
            ->select('*')->get();

        $data = array(
            'email'=>$patient[0]->email,
        );

        $name =$patient[0]->firstname.' '.$patient[0]->lastname;

        Mail::send('emails.confirmreg',
        ['name'=>$name,'data'=>$data], function ($message)
            use ($data)
        {
            $message->to($data['email']);
        });

        return response()->json('status : send email');
    }

    public function cancelAppt($itr){
        $data = array(
            'email'=>$itr->email,
        );

        $patient_name = $itr->patient_firstname.' '.$itr->patient_lastname;
        $doctor_name = $itr->doctor_firstname.' '.$itr->doctor_lastname;
        $date = $itr->date;
        $time = $itr->time;

        Mail::send('emails.cancel',
        ['patient_name'=>$patient_name,'doctor_name'=>$doctor_name,
            'date'=>$date,'time'=>$time,'data'=>$data], function ($message)
            use ($data)
        {
            $message->to($data['email']);
        });

        return response()->json('status : send email');
    }

}

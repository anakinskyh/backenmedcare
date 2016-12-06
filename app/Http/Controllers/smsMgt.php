<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use GuzzleHttp;
use Illuminate\Support\Facades\DB;

class smsMgt extends Controller
{
    //
    public function balance(Request $request){
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', 'https://sms.gipsic.com/api/balance',
            [
                'form_params' => [
                    'key'=>'Zv28e4mM6QtS0f833jYrwkI342n1Cf3M',
                    'secret'=>'M0AN639Uj39k6S1gCysYkfrjyX9rXD6N',
                    ]
            ] );


        //$res = $this->sendSms('0899172636','Hi, Friend!');

        return response()->json($res->getBody());
    }

    public function sendSms($phonenumber,$msg)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', 'https://sms.gipsic.com/api/send',
            [
                'form_params' => [
                    'key'=>'Zv28e4mM6QtS0f833jYrwkI342n1Cf3M',
                    'secret'=>'M0AN639Uj39k6S1gCysYkfrjyX9rXD6N',
                    'phone'=>$phonenumber,
                    'message'=>$msg]
            ] );


        //$res = $this->sendSms('0899172636','Hi, Friend!');

        return response()->json($res->getBody());
    }

    public function signupnoti($patient_id){
        $patient = DB::table('Patient')
            ->where('id','=',$patient_id)
            ->select('*')->get();

        $phone = $patient[0]->tel;
        $name = $patient[0]->firstname.' '.$patient[0]->lastname;
        $msg = "คุณ".$name." ได้ทำการสมัครสมาชิกกับทางโรงพยาบาล medcare\nขอขอบคุณค่ะ";
        smsMgt::sendSms($phone,$msg);
    }

    public function sendtopatient($doctor_id,$patient_id,$date,$time){


        $patient = DB::table('Patient')
            ->where('id','=',$patient_id)
            ->select('*')
            ->get();

        $doctor = DB::table('Staff')
            ->where('id','=',$doctor_id)
            ->select('*')
            ->get();

        $doctor_name = $doctor[0]->firstname.' '.$doctor[0]->lastname;

        $department = DB::table('Department')
            ->where('id','=',$doctor[0]->department_id)
            ->select('*')
            ->get();

        $msg = "คุณได้ทำรายการนัดกับคุณหมอ".$doctor_name." แผนกศัลยกรรม วันที่ ".$date
            ." ช่วง".$time
            ." ขอขอบคุณสำหรับการใช้บริการค่ะ";

        $phone = $patient[0]->tel;

        smsMgt::sendSms($phone,$msg);
    }

    public function resetpassword($patient_id){

        $patient = DB::table('Patient')
            ->where('id','=',$patient_id)
            ->select('*')->get();

        $number = $patient[0]->tel;

        $msg = "รหัสผ่านใหม่ของคุณคือ : ".$patient[0]->password;

        smsMgt::sendSms($number,$msg);
    }

    public function resetstaffpassword($patient_id){

        $patient = DB::table('Staff')
            ->where('id','=',$patient_id)
            ->select('*')->get();

        $number = $patient[0]->tel;

        $msg = "รหัสผ่านใหม่ของคุณคือ : ".$patient[0]->password;

        smsMgt::sendSms($number,$msg);
    }

    public function cancelAppt($itr){
        $number = $itr->tel;
        
        $msg = "เรียนคุณ".$itr->patient_firstname.' '.$itr->patient_lastname.
            ' คุณหมอ'.$itr->doctor_firstname.' '.$itr->doctor_lastname.
            ' ได้ทำการยกเลิกนัดของคุณในวันที่ '.$itr->date.' ในช่วง'.$itr->time.
            ' กรุณาเข้าสู่ระบบเพื่อทำนัดใหม่ หรือ ติดต่อทางโรงพยาบาลค่ะ';

        smsMgt::sendSms($number,$msg);
    }

}

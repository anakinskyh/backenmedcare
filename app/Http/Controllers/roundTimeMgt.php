<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;

class roundTimeMgt extends Controller
{
    //
    public function addRoundTime(Request $request){
        $validator = Validator::make($request->all(),[
            'doctor_id'=>'required|exists:Staff,id',
            'date'=>'required',
            'time'=>'required',
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $input = $request->only('doctor_id','date','time');
        $cond = DB::table('RoundTime')
            ->where('doctor_id','=',$input['doctor_id'])
            ->where('date','=',$input['date'])
            ->where('time','=',$input['time'])
            ->select('*')
            ->get();

        if(sizeof($cond)!=0)
            return response()->json(['status'=>'bad']);

        DB::table('RoundTime')
            ->insert($input);

        return response()->json(['status'=>'done']);
    }

    public function cancelRoundTime(Request $request){
        $validator = Validator::make($request->all(),[
            'doctor_id'=>'required|exists:Staff,id',
            'date'=>'required',
            'time'=>'required',
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $input = $request->only('doctor_id','date','time');


        DB::table('RoundTime')
            ->where('doctor_id','=',$input['doctor_id'])
            ->where('date','=',$input['date'])
            ->where('time','=',$input['time'])
            ->delete();

        $appointment = DB::table('Appointment')
            ->where('Appointment.doctor_id','=',$input['doctor_id'])
            ->where('Appointment.date','=',$input['date'])
            ->where('Appointment.time','=',$input['time'])
            ->join('Staff','Staff.id','=','Appointment.doctor_id')
            ->join('Patient','Patient.id','=','Appointment.patient_id')
            ->select('Patient.email','Patient.firstname AS patient_firstname',
                'Patient.lastname AS patient_lastname','Patient.tel',
                'Staff.firstname AS doctor_firstname',
                'Staff.lastname AS doctor_lastname',
                'Appointment.date','Appointment.time')->get();

        foreach($appointment as $itr){
            app('App\Http\Controllers\emailMgt')->cancelAppt($itr);
            app('App\Http\Controllers\smsMgt')->cancelAppt($itr);
        }

         $appointment = DB::table('Appointment')
            ->where('Appointment.doctor_id','=',$input['doctor_id'])
            ->where('Appointment.date','=',$input['date'])
            ->where('Appointment.time','=',$input['time'])
            ->delete();
/*
        $input1 = ['doctor_id'=>$input['doctor_id'],
            'start'=>new date()];

        $staff = DB::table('Staff')
            ->where('Staff.id','=',$input['doctor_id'])
            ->get();

        $input2 = ['department_id'=>$staff[0]->department_id,
            'start'=>new date()];

        foreach ($appointment as $itr){

            $ischeck = false;

            $freetime = roundTimeMgt::getAva($input1);
            foreach($freetime as $afree){
                $res = DB::table('Appointment')
                    ->where('patient_id','=',$itr->patient_id)
                    ->where('date','=',$afree->date)
                    ->where('time','=',$afree->time)
                    ->get();
                if(sizeof($res)==0){
                    DB::table('Appointment')
                        ->->where('date','=',$input['date'])
                        ->where('time','=',$input['time']);
                }
            }

            $freetime = roundTimeMgt::getAva($input2);
            foreach($freetime as $afree){
                
            }

            return  response()->json($itr->id);
        }
*/
        return response()->json(['status'=>'done']);
    }

    public function getRoundTime(Request $request){
        $input = $request->all();

        $roundtime = DB::table('RoundTime');

        if(array_key_exists('doctor_id',$input))
            $roundtime = $roundtime->where('RoundTime.doctor_id','=',$input['doctor_id']);

        if(array_key_exists('start',$input))
            $roundtime = $roundtime->where('RoundTime.date','=',$input['start']);

        if(array_key_exists('end',$input))
            $roundtime = $roundtime->where('RoundTime.date','=',$input['end']);

        $roundtime =$roundtime->select('*')->get();

        return response()->json($roundtime);
    }

    public function getFastest($doctor_id){
        $date = new DateTime();

        $request = new Request();
        $request['start']=$date;
        $request['doctor_id']=$doctor_id;

        app('App\Http\Controllers\scheduleMgt0')->getAva($request);
    }

    public function getAva($input){

        $table = DB::table('RoundTime')->join('Staff','RoundTime.doctor_id','=','Staff.id')
            ->join('Department','Department.id','=','Staff.department_id');

        if(array_key_exists('start',$input)){
            $table = $table->where('RoundTime.date','>=',$input['start']);
        }

        if(array_key_exists('end',$input)){
            $table = $table->where('RoundTime.date','<=',$input['end']);
        }

        if(array_key_exists('time',$input)){
            $table = $table->where('RoundTime.time','=',$input['time']);
        }

        if(array_key_exists('department_id',$input)){
            $table = $table->where('Staff.department_id','=',$input['department_id']);
        }else if(array_key_exists('doctor_id',$input)){
            $table = $table->where('Staff.id','=',$input['doctor_id']);
        }

        $table = $table->leftjoin('Appointment',function($join){
                $join->on('Appointment.date','=','RoundTime.date');
                $join->on('Appointment.time','=','RoundTime.time');
                $join->on('Appointment.doctor_id','=','RoundTime.doctor_id');
            })
            
            ->select('Staff.id','Staff.firstname','Staff.lastname','RoundTime.date'
                ,'RoundTime.time',DB::raw('count(*) as counter'))
            ->groupBy('Staff.id','Staff.firstname','Staff.lastname','RoundTime.date'
                ,'RoundTime.time')

            ->having('counter','<', '15')
            ;

        return $table;
    }
}

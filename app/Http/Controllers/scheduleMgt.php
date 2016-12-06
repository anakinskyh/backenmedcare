<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class scheduleMgt extends Controller
{
    //get doctor_id or dep_id
    public function getAvailableDateTime(Request $request){

        $validator = Validator::make($request->all(),[
            'start'=>'required|after:'.Carbon::now(),
            'end'=>'required',
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $input = $request->all();

        $result = array();

        if(array_key_exists('doctor_id',$input)){
            $result_ = $this->getAvailableDateTimeFromDoctor(
                $input['doctor_id'],$input['start'],$input['end']
            );
            $result[] = $result_;

        }else if(array_key_exists('dep_id',$input)){
            $all_doctor_id = DB::select(DB::raw(
                'SELECT id FROM Staff
                 WHERE department_id = :dep_id
                 AND role = doctor'
                ),$input);

            foreach ($all_doctor_id as $value) {
                $result_ = $this->getAvailableDateTimeFromDoctor(
                    $value, $input['start'], $input['end']
                );
                $result[] = $result_;
            }
        }else{
        }

        return response()->json($result);
    }
    public function showScheduleForMed(Request $request){
        $validator = Validator::make($request->all(),[
            'start'=>'required|after:'.Carbon::now(),
            'end'=>'required',
            'doctor_id'=>'required',
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $result = DB::select(DB::raw(
            'SELECT Appointment.syntom,
              Appointment.start,
              Appointment.id,
              CONCAT(Patient.firstname,Patient.lastname)
              AS name
            FROM Appointment
            WHERE start>=:start
            AND statr <= :end 
            INNER JOIN Patient
            ON Appointment.patient_id=Patient.id'
        ));

        response()->json($result);
    }

    public function getAvailableDateTimeFromDoctor($doctor_id,$start,$end){
        $input = array('doctor_id'=>$doctor_id,
            'start'=>$start,
            'end'=>$end,
        );

        $name = DB::select(DB::raw("SELECT firstname,lastname 
          FROM Staff 
          WHERE id = :doctor_id"),$input);

        //get unavailable
        $count = DB::select(DB::raw('SELECT start,Count(*) as Count
            FROM Appointment 
            WHERE doctor_id = :doctor_id
            AND start>=:start
            AND start<=:end
            GROUP BY start'),$input);

        $unavailable = array();
        foreach ($count as $key => $value){
            if($value == 15)
                $unavailable[] = $key;
        }

        //get all wr
        $allWR = DB::select(DB::raw('SELECT start
            FROM RoundTime
            WHERE doctor_id = :doctor_id
            AND start>=:start
            AND start<=:end'),$input);

        $available = array_diff($allWR,$unavailable);

        $result = array(
            'doctor_id'=>$doctor_id,
            'name'=>$name,
            'available'=>$available
        );

        return $result;
    }
    public function isAvailableDateTime($doctor_id,$start){
        $input = array(
            'doctor_id'=>$doctor_id,
            'start'=>$start
        );

        //get unavailable
        $count = DB::select(DB::raw('SELECT Count(*) as No
            FROM Appointment 
            WHERE doctor_id = :doctor_id
            AND start=:start'),$input);

        /*
        $patient_num = $count[0]
        */
        if($count[0]->No == 15)
            return false;
        return true;
    }

    public function getAva(Request $request){
        $input = $request->all();

        $ava = DB::table('RoundTime')
            ->join('Staff','Staff.id','=','RoundTime.doctor_id')
            ->join('Department','Department.id','=','Staff.department_id');

        if(array_key_exists('department_id',$input))
        {
            $ava = $ava->where('Staff.department_id','=',$input['department_id']);
        }

        if(array_key_exists('doctor_id',$input)){
            $ava = $ava->where('RoundTime.doctor_id','=',$input['doctor_id']);
        }

        if(array_key_exists('start',$input)){
            $ava = $ava->where('RoundTime.date','>=',$input['start']);
        }

        if(array_key_exists('end',$input)){
            $ava = $ava->where('RoundTime.date','<=',$input['end']);
        }

        if(array_key_exists('time',$input)){
            $ava = $ava->where('RoundTime.time','=',$input['time']);
        }

        /*check 15*/


        $ava = $ava->select("RoundTime.*",'Staff.firstname AS doctor_firstname'
            ,'Staff.lastname AS doctor_lastname');

        return response()->json($ava->get());
    }

    public function getAvailableDateTimeWithCount(Request $request){
        //
        $input = $request->all();

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

        return response()->json($table->get());
    }


}

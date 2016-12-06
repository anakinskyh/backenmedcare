<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class dispensationMgt extends Controller
{
    //
    public function getDispensationList(Request $request){

        $input = $request->all();

        $dispensation = DB::table('Dispensation')
            ->join('HealthRecord','Dispensation.hr_id','=','HealthRecord.id')
            ->join('Appointment','Appointment.id','=','HealthRecord.appointment_id')
            ->join('Patient','Patient.id','=','Appointment.patient_id')
            ->join('Staff','Appointment.doctor_id','=','Staff.id');

        if(array_key_exists('patient_id',$input)){
            $dispensation = $dispensation->where('Appointment.patient_id','='
                ,$input['patient_id']);
        }

        if(array_key_exists('start',$input)){
            $dispensation = $dispensation->where('Appointment.date','>='
                ,$input['start']);
        }

        if(array_key_exists('end',$input)){
            $dispensation = $dispensation->where('Appointment.date','>='
                ,$input['end']);
        }

        if(array_key_exists('appointment_id',$input)){
            $dispensation = $dispensation->where('Appointment.id','='
                ,$input['appointment_id']);
        }

        $dispensation = $dispensation->select('Dispensation.*',
            'Dispensation.id AS dispensation_id',
            'Appointment.id AS appointment_id',
            'HealthRecord.id AS healthrecord_id',
            'Patient.firstname AS patient_firstname',
            'Patient.lastname AS patient_lastname',
            'Appointment.date AS date',
            'Appointment.time AS time',
            'Staff.firstname AS doctor_firstname',
            'Staff.lastname AS doctor_lastname',
            'Staff.id AS doctor_id',
            'Patient.id AS patient_id');

        return response()->json($dispensation->get());
    }
    public function getDispensation(Request $request){
        $validator = Validator::make($request->all(),[
            'dispensation_id'=>'required|exists:Dispensation,id',
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $did = $request->all()['dispensation_id'];
        $dispensation = DB::table('Dispensation')
            ->where('Dispensation.id','=',$did)
            ->join('HealthRecord','HealthRecord.id','=','Dispensation.hr_id')
            ->join('Appointment','Appointment.id','=','HealthRecord.appointment_id')
            ->join('Patient','Patient.id','=','Appointment.patient_id')
            ->join('Staff','Staff.id','=','Appointment.doctor_id')
            ->select('Dispensation.*','HealthRecord.*','Appointment.*'
                ,'Patient.firstname AS patient_firstname'
                ,'Patient.lastname AS patient_lastname'
                ,'Staff.firstname AS doctor_firstname'
                ,'Staff.lastname AS doctor_lastname')
                ->get();

        $druglist = DB::table('DrugList')
            ->where('dispensation_id','=',$did)
            ->join('Drug','DrugList.drug_id','=','Drug.id')
            ->select('DrugList.*','Drug.*')
            ->get();

        return response()->json([
            'dispensation'=>$dispensation,
            'drug_list'=>$druglist,
        ]);
    }

    public function addDispensation(Request $request){
        //code
        $validator = Validator::make($request->all(),[
            'hr_id'=>'required|exists:HealthRecord,id',
            'druglist'=>'required'
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $hr_id = $request->only('hr_id');
        DB::table('Dispensation')
            ->insert($hr_id);

        $dispensation = DB::table('Dispensation')
            ->where('hr_id','=',$hr_id['hr_id'])
            ->select('*')->get();
        $dispensation_id = $dispensation[0]->id;

        $druglist = $request->all()['druglist'];

        //return response()->json($druglist);

        foreach($druglist as $drug) {
            //return response()->json($drug['drug_id']);
            $input = ['drug_id'=>$drug['drug_id'],
            'dispensation_id'=>$dispensation_id,
                'amount'=>$drug['amount']];
            DB::table('DrugList')
                ->insert($input);
        }

        return response()->json(['status'=>'done','txt'=>$druglist]);
    }

    public function editDispensation(Request $request){
        //code
    }

    public function dispense(Request $request){
            $validator = Validator::make($request->all(),[
            'dispensation_id'=>'required|exists:Dispensation,id',
            'phar_id'=>'required|exists:Staff,id'
        ]);

        if($validator->fails())
            return $validator->errors()->all();

        $input = $request->all();
        DB::table('Dispensation')
            ->where('id','=',$input['dispensation_id'])
            ->update(['phar_id'=>$input['phar_id']]);

        return response()->json(['status'=>'done']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class etc extends Controller
{
    //
    public function getDoctorNameByDepname(Request $request){
        //return response()->json($request->all());

        //file_put_contents('log.txt',$request->all());

        File::put('log.txt',$request->all());

        $validator = Validator::make($request->all(),[
            'deptname'=>'required|exists:Department,name'
        ]);

        if($validator->fails())
            return response()->json($validator->errors()->all());

        $input = $request->all();
        $result = DB::select("SELECT
              Staff.firstname,
              Staff.lastname,
              Staff.id,
              Staff.role
            FROM Staff
            INNER JOIN Department
            ON Staff.department_id = Department.id
            WHERE Department.name = :deptname
            AND Staff.role = 'doctor'
        ",$input);

        return response()->json($result);
    }

    public function getDepartment(Request $request){
        //return response()->json('hi');
        $result = DB::select(DB::raw('
            SELECT *
            FROM Department
        '),[]);

        return response()->json($result);
    }
}

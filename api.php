<?php

use Illuminate\Http\Request;

header('Access-Control-Allow-Origin: *');
header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/v1/testmailpatientreset','demo@testResetPWDEmail');

Route::get('/v1/sayhidb',function(){

    $results = DB::select( DB::raw("SELECT * FROM Appointment") );
    return $results;
});
Route::post('/v1/sayhi',function (){
    return response()->json("Hi, I'm api.");
});
Route::get('/v1/sayhi',function (){
    return "Hi, I'm api.";
});

Route::get('/v1/demoEmail','demo@demo');
Route::get('/v1/getsmsbalance','smsMgt@balance');
Route::get('/v1/demosms','demo@demeSms');

Route::post('/v1/sendtopatient','emailMgt@demo');
Route::post('/v1/demoregconfirm',function (Request $request){
    $pid = $request->all()['patient_id'];
    app('App\Http\Controllers\emailMgt')->signupnoti($pid);
});

Route::post('/v1/showEditAppt','apptMgt@showEditAppt');

//userMgt
Route::post('/v1/confirmadduser','userMgt@confirmAddUser');

//signup
Route::post('/v1/signup','userMgt@confirmAddPatient');

//signin
Route::post('/v1/peasysignin','userMgt@patientEasySignin');
Route::post('/v1/seasysignin','userMgt@staffEasySignin');

//apptMgt
Route::post('/v1/getpatientapptlist','apptMgt@showApptListByPatientID');
Route::post('/v1/getdoctorapptlist','apptMgt@showApptListByDoctorID');
Route::post('/v1/getapptlist','apptMgt@getApptList');
Route::post('/v1/confirmappt','apptMgt@confirmAppt');

//etc
Route::post('/v1/getdoctorname','etc@getDoctorNameByDepname');
Route::post('/v1/getdepartment','etc@getDepartment');

//user
Route::post('/v1/getpatientinfo','userMgt@getPatientInfo');
Route::post('/v1/editpatientinfo','userMgt@editPatientInfo');
Route::post('/v1/changepassword','userMgt@editPatientInfo');
Route::post('/v1/resetpatientpwd','userMgt@resetPatientPWD');
Route::post('/v1/resetstaffpwd','userMgt@resetStaffPWD');

 //HealthRecord
 Route::post('/v1/addhealthrecord','healthRecordMgt@addHealthRecord');
 Route::post('/v1/edithealthrecord','healthRecordMgt@editHealthRecord');
 Route::post('/v1/gethrlistbydoctorid','healthRecordMgt@getHRListByDoctorID');
 Route::post('/v1/gethrbyid','healthRecordMgt@getHRByID');
 Route::post('/v1/gethrbyapptid','healthRecordMgt@getHRByAppID');

//schedule getAva
Route::post('/v1/getavailabletime','scheduleMgt@getAva');
Route::post('/v1/getavawithcount','scheduleMgt@getAvailableDateTimeWithCount');

//dispensation getDispensationList
Route::post('/v1/getdispensationlist','dispensationMgt@getDispensationList');
Route::post('/v1/getdispensation','dispensationMgt@getDispensation');//
Route::post('/v1/adddispensation','dispensationMgt@addDispensation');//addDispensation
Route::post('/v1/dispense','dispensationMgt@dispense');

//roundtime
Route::post('/v1/addroundtime','roundTimeMgt@addRoundTime');
Route::post('/v1/cancelroundtime','roundTimeMgt@cancelRoundTime');
Route::post('/v1/getroundtime','roundTimeMgt@getRoundTime');

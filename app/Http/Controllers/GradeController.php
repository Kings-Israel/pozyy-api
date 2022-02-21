<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $grades = Grade::where([['school_id', null]])->with("subjects")->get();

        return response()->json([
            "grades"=>$grades,
        ], 200);
    } //end index

    /**
     * Store a newly created resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return true
     */
    public function store(Request $request){
        $validatedData =  Validator::make($request->all(),[
            'name' => 'required | unique:grades',
        ]);
    
        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }

        $grade=Grade::create([
            'name' => $request->name,
        ]);
        // //attach subjects
        // if($request->subject_ids){
        //     $grade->subjects()->attach($request->subject_ids);
        // }
    
        return response()->json([
            "success"=>true,
        ], 200);
    }//end store

    /**
     * Display the specified resource, with list of subjects
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        $grade = Grade::where("id", $id)->with("subjects.topics")->first();

        return response()->json([
            "grade"=>$grade,
        ], 200);
    } //end show


}

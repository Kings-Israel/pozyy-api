<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Store a newly created resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return true
     */
    public function store(Request $request){
        $validatedData =  Validator::make($request->all(),[
            'name' => 'required',
            'grade_id' => 'required | exists:grades,id',
        ]);
    
        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }

        $subject=Subject::create([
            'name' => $request->name,
            'grade_id' => $request->grade_id,
        ]);
        // $discussion->categories()->attach($request->category_ids);
    
        return response()->json([
            "success"=>true,
        ], 200);
    }//end store

    /**
     * Display the specified resource, with list of topics
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        $subject = Subject::where("id", $id)->with("topics.subtopics")->first();

        return response()->json([
            "subject"=>$subject,
        ], 200);
    } //end show
}

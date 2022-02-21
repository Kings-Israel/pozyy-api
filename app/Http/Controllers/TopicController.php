<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Store a newly created resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return true
     */
    public function store(Request $request){
        $validatedData =  Validator::make($request->all(),[
            'name' => 'required | unique:topics',
            'subject_id' => 'required | exists:subjects,id',
        ]);
    
        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }

        $topic=Topic::create([
            'name' => $request->name,
            'subject_id' => $request->subject_id,
        ]);
        // $discussion->categories()->attach($request->category_ids);
    
        return response()->json([
            "success"=>true,
        ], 200);
    }//end store

    /**
     * Display the specified resource, with list of Sub topics
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        $topic = Topic::where("id", $id)->with("subtopics")->first();

        return response()->json([
            "topic"=>$topic,
        ], 200);
    }
    public function edit_topic(Request $request, $id) {
        $topic = Topic::where('id', $id)->update([
            'name' => $request->name
        ]);
        return response()->json('Topic editted successfully');
    }
}

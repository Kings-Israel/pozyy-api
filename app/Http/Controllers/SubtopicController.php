<?php

namespace App\Http\Controllers;

use App\Models\Subtopic;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SubtopicController extends Controller
{
    /**
     * Store a newly created resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return true
     */
    public function store(Request $request){
        $validatedData =  Validator::make($request->all(),[
            'name' => 'required | unique:subtopics',
            'topic_id' => 'required | exists:topics,id',
        ]);
    
        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }

        $subtopic=Subtopic::create([
            'name' => $request->name,
            'topic_id' => $request->topic_id,
        ]);
        // $discussion->categories()->attach($request->category_ids);
    
        return response()->json([
            "success"=>true,
        ], 200);
    }
    public function edit_subtopic(Request $request, $id) {
        $subtopic = Subtopic::where('id', $id)->update([
            'name' => $request->name
        ]);
        return response()->json('Subtopic editted successfully');
    }
}

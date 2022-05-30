<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with('grade')->where('school_id', auth()->user()->school_id)->get();

        return pozzy_httpOk($subjects);
    }
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
            'school_id' => auth()->user()->school_id
        ]);

        return pozzy_httpOk($subject);
    }//end store

    /**
     * Display the specified resource, with list of topics
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        $subject = Subject::where("id", $id)->first();

        return pozzy_httpOk($subject);
    } //end show

    public function destroy($id)
    {
        $subject = Subject::find($id);

        $subject->delete();

        return pozzy_httpOk($subject);
    }

    public function getGradeSubjects($id)
    {
        $subjects = Subject::with('grade')
                        ->where('school_id', auth()->user()->school_id)
                        ->where('grade_id', $id)
                        ->get();

        return pozzy_httpOk($subjects);
    }
}

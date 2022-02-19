<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestCategory;
use App\Http\Resources\Question as QuestionResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tests = Test::where('category', 0)->with(["user", "grade", "subject", "category"])->withCount("questions")->get();

        return response()->json([
            "tests"=>$tests,
        ], 200);
    } //end index

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function testCategories()
    {
        $tests_categories = TestCategory::all();

        return response()->json([
            "categories"=>$tests_categories,
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
            'name' => 'required',
            'time' => 'required',
            'term' => 'required',
            'no_questions' => 'required',
            'test_category_id' => 'required | exists:test_categories,id',
            'grade_id' => 'required | exists:grades,id',
            'subject_id' => 'required | exists:subjects,id',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }
        $loggedUser = Auth::User();

        if($request->system_generated) {
            $cec = $request->system_generated;
            $category = 1;
        } else {
            $cec = 0;
            $category = 0;
        }

        $rand = mt_rand(10000, 99999);
        $serial_no = "T".$rand."POZZY";

        $test=Test::create([
            'name' => $request->name,
            'serial_no' => $serial_no,
            'term' => $request->term,
            'time' => $request->time,
            'no_questions' => $request->no_questions,
            'test_category_id' => $request->test_category_id,
            'grade_id' => $request->grade_id,
            'subject_id' => $request->subject_id,
            'topic_id' => $request->topic_id,
            'created_by' => $loggedUser->id,
            'system_generated' => $cec,
            'category' => $category
        ]);

        return response()->json([
            "success"=>true,
        ], 200);
    }//end store

    /**
     * Display the specified resource, with list of questions
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        $test = Test::where("id", $id)->with(["questions", "user", "grade", "subject", "topic", "category"])->first();

        $test_data = [
            'id' => $test->id,
            'name' => $test->name,
            'serial_no' => $test->serial_no,
            'term' => $test->term,
            'time' => $test->time,
            'no_questions' => $test->no_questions,
            'test_category' => $test->category->name,
            'grade' => $test->grade->name,
            'grade_id' => $test->grade->id,
            'subject' => $test->subject->name,
            'subject_id' => $test->subject->id,
            // 'topic_id' => $test->topic->id,
            'created_by' => $test->user->username,
            "questions" => QuestionResource::collection($test->questions)
        ];
        return response()->json([
            "test"=>$test_data,
        ], 200);
    } //end show

}

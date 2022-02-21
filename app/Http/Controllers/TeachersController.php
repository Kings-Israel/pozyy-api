<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Question,Subject,Topic,Grade,Subtopic,Test,GeneratedQuestion};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Auth;

class TeachersController extends Controller
{
    public function all_questions() {
        $questions = Question::orderBy('id', 'desc')->with(['grade', 'subject', 'topic', 'subtopic', 'answers', 'solution', 'image'])
                ->get();
        return response()->json([$questions]);
    }
    public function all_grades() {
        $sub = Grade::where('school_id', null)->get();
        return pozzy_httpOk($sub);
    }
    public function get_subjects(Request $request, $grade_id) {
        $sub = Grade::where('school_id', null)->get('id');
        if($sub) {
            $data = Subject::where('grade_id', $grade_id)->whereIn('grade_id', $sub)->get();
            return pozzy_httpOk($data);
        }
        return pozzy_httpBadRequest('Oop, no data found');
    }
    public function get_topics(Request $request, $subject_id) {
        $top = Topic::where('subject_id', $subject_id)->get();
        return pozzy_httpOk($top);
    }
    public function get_subtopics(Request $request, $topic_id) {
        $sub_top = Subtopic::where('topic_id', $topic_id)->get();
        return pozzy_httpOk($sub_top);
    }
    public function filter_questions(Request $request) {
        $questions = Question::orderBy('id', 'desc')
                        ->where([['grade_id', $request->grade_id], ['subject_id', $request->subject_id], ['topic_id', $request->topic_id]])
                        ->with(['grade', 'subject', 'topic', 'subtopic', 'answers', 'solution', 'image'])
                ->get();
        return response()->json([$questions]);
    }
    public function create_exam(Request $request) {
        $questions = $request->questions;

        $quiz = array_replace($questions);
        $cec = Arr::pluck($quiz, 'question.id'); 

        $random = Arr::random($cec);
        $randomize = Question::find($random);
        $data = [];
        foreach($cec as $get_topic_id) {
            $top = Topic::find($get_topic_id);
            array_push($data, ['name' => $top->name, 'id' => $top->id]);
        }

        $loggedUser = Auth::User();
        $rand = mt_rand(10000, 99999);
        $serial_no = "T".$rand."POZZY";

        $test = Test::create([
            'name' => $request->name,
            'serial_no' => $serial_no,
            'term' => $request->term,
            'time' => $request->time,
            'no_questions' => count($request->questions),
            'test_category_id' => $request->test_category_id,
            'grade_id' => $randomize->grade_id,
            'subject_id' => $randomize->subject_id,
            'topic_id' => $data,
            'created_by' => $loggedUser->id,
            'system_generated' => 0,
            'category' => 0,
            'school_id' => $loggedUser->school_id
        ]);
        foreach($cec as $result) {
            $get_quiz = Question::where('id', $result)
                        ->with('grade', 'subject', 'topic', 'subtopic', 'answers', 'solution', 'image')
                        ->first();
            $der = new GeneratedQuestion;
            $der->test_id = $test->id;
            $der->question_name = $get_quiz->question;
            $der->grade_name = $get_quiz->grade['name'];
            $der->subject_name = $get_quiz->subject['name'];
            $der->topic_name = $get_quiz->topic['name'];
            $der->subtopic_name = $get_quiz->subtopic['name'];
            $der->answers = $get_quiz->answers;
            $der->solution = $get_quiz->solution;
            $der->image = $get_quiz->image;
            $der->save();
        }
        return pozzy_httpCreated('Test created successfully');
    }
    public function get_tests() {
        $user = Auth::User();
        $test = Test::orderBy('id', 'desc')->where([['school_id', $user->school_id], ['created_by', $user->id]])->get(['name','serial_no','time','term','no_questions','created_at']);
        return pozzy_httpOk($test);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{GeneratedQuestion, Test, Question};
use Illuminate\Support\Arr;

class GeneratedQuestionsController extends Controller
{
    public function generated_questions() {
        $tests = Test::where('system_generated', 2)->get();
        $data = [];
        foreach($tests as $test) {
            $number_questions = $test->no_questions;
            $grade = $test->grade_id;
            $subject = $test->subject_id;
            $topic = $test->topic_id;
            if($topic != null) {
                $bun = $test->topic_id;
                $ids = Arr::pluck($bun, 'id');
                $questions = Question::where([['grade_id', $grade], ['subject_id', $subject]])
                    ->orderBy('id', 'desc')
                    ->whereIn('topic_id', $ids)
                    ->inRandomOrder()->limit($number_questions)
                    ->with('grade', 'subject', 'topic', 'subtopic', 'answers', 'solution', 'image')->get();
            } else {
                $questions = Question::where('grade_id', $grade)->where('subject_id', $subject)
                    ->orderBy('id', 'desc')
                    ->inRandomOrder()->limit($number_questions)
                    ->with('grade', 'subject', 'topic', 'subtopic', 'answers', 'solution', 'image')->get();
            }
            array_push($data, $questions);
            foreach($questions as $question) {
                $quiz = new GeneratedQuestion;
                $quiz->test_id = $test->id;
                $quiz->question_name = $question->question;
                $quiz->grade_name = $question->grade['name'];
                $quiz->subject_name = $question->subject['name'];
                $quiz->topic_name = $question->topic['name'];
                $quiz->subtopic_name = $question->subtopic['name'];
                // $quiz->image_path = $question->image['image_path'];
                $quiz->answers = $question->answers;
                $quiz->solution = $question->solution;
                $quiz->image = $question->image;
                $quiz->save();
            }
            $test->update([
                'system_generated' => 1
            ]);
        }
        return response()->json($data);
    }
    public function get_generated_questions() {
        $tests = Test::where('category', 1)->with(['grade', 'subject', 'system_questions'])->withCount('der')->get();
        return response()->json($tests);
    }
}

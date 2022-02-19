<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Image;
use App\Models\Solution;
use App\Models\Answer;
use App\Models\Test;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image2;
use App\Http\Resources\Question as QuestionResource;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $questions = Question::orderBy('id', 'desc')->with(['grade', 'subject', 'topic', 'subtopic', 'answers', 'solution', 'image'])
        // ->select(DB::raw('DATE_FORMAT(cust.cust_dob, "%d-%b-%Y") as formatted_dob')
        ->get();

        return QuestionResource::collection($questions);
    } //end index

    /**
     * Display the specified resource
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        $question = Question::where("id", $id)->with(['grade', 'subject', 'topic', 'subtopic', 'answers', 'solution', 'image'])->first();

        return new QuestionResource($question);
    } //end show

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function questionsByUser(Request $request)
    {
        $loggedUser = Auth::User();

        $questions = Question::with(['grade', 'subject', 'topic', 'subtopic', 'answers', 'solution', 'image'])->where("created_by",$loggedUser->id)->get();

        return QuestionResource::collection($questions);
    } //end index

    /**
     * Store a newly created resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return true
     */
    public function store(Request $request){
        $validatedData =  Validator::make($request->all(),[
            'question' => 'required',
            'grade_id' => 'required | exists:grades,id',
            'subject_id' => 'required | exists:subjects,id',
            'topic_id' => 'required | exists:topics,id',
            'subtopic_id' => 'required | exists:subtopics,id',
            'answerA' => 'required',
            'answerB' => 'required',
            'answerC' => 'required',
            'answerD' => 'required',
            'correctAnswer' => 'required',
            'explanation' => 'required',

            //made custom rule in boot(AppServiceProvider) to check base64 image
            // 'image' => 'imageable'

            // 'solution_image' => 'optional|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }
        $loggedUser = Auth::User();


        $question=Question::create([
            'question' => $request->question,
            'grade_id' => $request->grade_id,
            'subject_id' => $request->subject_id,
            'topic_id' => $request->topic_id,
            'subtopic_id' => $request->subtopic_id,
            'created_by' => $loggedUser->id,
        ]);

        $correctAnswer = $request->correctAnswer;

        //answer A
        if($correctAnswer=="A"){
            $answerAarray = [
                "answer" => $request->answerA,
                "is_answer" => true
            ];
        }else{
            $answerAarray = [
                "answer" => $request->answerA,
                "is_answer" => false
            ];
        }
        $answerA = $question->answers()->create($answerAarray);

        //answer B
        if($correctAnswer=="B"){
            $answerBarray = [
                "answer" => $request->answerB,
                "is_answer" => true
            ];
        }else{
            $answerBarray = [
                "answer" => $request->answerB,
                "is_answer" => false
            ];
        }
        $answerB = $question->answers()->create($answerBarray);

        //answer C
        if($correctAnswer=="C"){
            $answerCarray = [
                "answer" => $request->answerC,
                "is_answer" => true
            ];
        }else{
            $answerCarray = [
                "answer" => $request->answerC,
                "is_answer" => false
            ];
        }
        $answerC = $question->answers()->create($answerCarray);

        //answer D
        if($correctAnswer=="D"){
            $answerDarray = [
                "answer" => $request->answerD,
                "is_answer" => true
            ];
        }else{
            $answerDarray = [
                "answer" => $request->answerD,
                "is_answer" => false
            ];
        }
        $answerD = $question->answers()->create($answerDarray);



        //Upload Question image if exists
        if($file=$request->get('image')){
        // if($file=$request->file('image')){
            $filename = $question->id."-".rand(0,100);
            // $extension = $file->extension();

            $img = Image2::make($file);

            $mime = $img->mime();
            if ($mime == 'image/jpeg')
                $extension = 'jpg';
            elseif ($mime == 'image/png')
                $extension = 'png';
            elseif ($mime == 'image/gif')
                $extension = 'gif';
            else
                $extension = '';

            $image_name = $filename .".". $extension;
            // $image_name = $filename;
            $path = 'uploaded_images/' . $image_name;

            //original
            if (!file_exists(public_path('uploaded_images' .  DIRECTORY_SEPARATOR) . $image_name)) {
                $img->save('uploaded_images/' . $image_name);
            }

            $image = $question->image()->create([
                'image' => $image_name,
                'image_path' => $path
            ]);

        }

        /****
         *
         *****  SOLUTION ****
         *
        *******/
        //If has solution text (explanation)
        $solution = $question->solution()->create([
            "explanation" => $request->explanation,
        ]);

        //If has solution image
        if($file=$request->file('solution_image')){
            $filename = $solution->id."-".rand(0,100);
            // $extension = $file->extension();
            $extension = $file->getClientOriginalExtension();
            $image_name = $filename .".". $extension;
            $path = 'solution_files/' . $image_name;

            //original
            if (!file_exists(public_path('solution_files' .  DIRECTORY_SEPARATOR) . $image_name)) {
                $file->move(public_path().'/solution_files/', $image_name);
            }

            $solution->update([
                'image' => $image_name,
                'image_path' => $path
            ]);

        }

        //If has solution audio
        if($file=$request->file('solution_audio')){
            $filename = $solution->id."-".rand(0,100);
            $extension = $file->getClientOriginalExtension();
            $audio_name = $filename .".". $extension;
            $path = 'solution_files/' . $audio_name;

            //original
            if (!file_exists(public_path('solution_files' .  DIRECTORY_SEPARATOR) . $audio_name)) {
                $file->move(public_path().'/solution_files/', $audio_name);
            }

            $solution->update([
                'audio' => $audio_name,
                'audio_path' => $path
            ]);

        }

        //If has solution video
        if($file=$request->file('solution_video')){
            $filename = $solution->id."-".rand(0,100);
            $extension = $file->getClientOriginalExtension();
            $video_name = $filename .".". $extension;
            $path = 'solution_files/' . $video_name;

            //original
            if (!file_exists(public_path('solution_files' .  DIRECTORY_SEPARATOR) . $video_name)) {
                $file->move(public_path().'/solution_files/', $video_name);
            }

            $solution->update([
                'video' => $video_name,
                'video_path' => $path
            ]);

        }

        //Check if it is an test question(exam/cat/homework)
        if($test_id = $request->get('test_id')){
            $test = Test::find($test_id);
            $test->questions()->attach($question);
        }


        return response()->json([
            "success"=>true,
        ], 200);
    }//end store


    public function update(Request $request, $id)
    {
        $validatedData =  Validator::make($request->all(),[
            'question' => 'required',
            'grade_id' => 'required | exists:grades,id',
            'subject_id' => 'required | exists:subjects,id',
            'topic_id' => 'required | exists:topics,id',
            'subtopic_id' => 'required | exists:subtopics,id',
            'answerA' => 'required',
            'answerB' => 'required',
            'answerC' => 'required',
            'answerD' => 'required',
            'correctAnswer' => 'required',
            'explanation' => 'required',

            //made custom rule in boot(AppServiceProvider) to check base64 image
            // 'image' => 'imageable'

            // 'image' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }

        $question = Question::where('id', $id)->with('image')->first();
        $question->update([
            'question' => $request->question,
            'grade_id' => $request->grade_id,
            'subject_id' => $request->subject_id,
            'topic_id' => $request->topic_id,
            'subtopic_id' => $request->subtopic_id,
        ]);

        Answer::where("question_id", $question->id)->delete();
        Solution::where("question_id", $question->id)->delete();

        $correctAnswer = $request->correctAnswer;

        //answer A
        if($correctAnswer=="A"){
            $answerAarray = [
                "answer" => $request->answerA,
                "is_answer" => true
            ];
        }else{
            $answerAarray = [
                "answer" => $request->answerA,
                "is_answer" => false
            ];
        }
        $answerA = $question->answers()->create($answerAarray);

        //answer B
        if($correctAnswer=="B"){
            $answerBarray = [
                "answer" => $request->answerB,
                "is_answer" => true
            ];
        }else{
            $answerBarray = [
                "answer" => $request->answerB,
                "is_answer" => false
            ];
        }
        $answerB = $question->answers()->create($answerBarray);

        //answer C
        if($correctAnswer=="C"){
            $answerCarray = [
                "answer" => $request->answerC,
                "is_answer" => true
            ];
        }else{
            $answerCarray = [
                "answer" => $request->answerC,
                "is_answer" => false
            ];
        }
        $answerC = $question->answers()->create($answerCarray);

        //answer D
        if($correctAnswer=="D"){
            $answerDarray = [
                "answer" => $request->answerD,
                "is_answer" => true
            ];
        }else{
            $answerDarray = [
                "answer" => $request->answerD,
                "is_answer" => false
            ];
        }
        $answerD = $question->answers()->create($answerDarray);

        //solution
        $solution = $question->solution()->create([
            "explanation" => $request->explanation,
        ]);

        //Upload image if exists
        if($file=$request->get('image')){
        // if($file=$request->file('image')){
            $image = $question->image;
            if($image){
                $image_name = $image->image;
                unlink('uploaded_images/' . $image_name);
                Image::where("imageable_id", $question->id)->where("imageable_type", "App\Models\Question")->delete();
            }

            $filename = $question->id."-".rand(0,100);
            // $extension = $file->extension();

            $img = Image2::make($file);

            $mime = $img->mime();
            if ($mime == 'image/jpeg')
                $extension = 'jpg';
            elseif ($mime == 'image/png')
                $extension = 'png';
            elseif ($mime == 'image/gif')
                $extension = 'gif';
            else
                $extension = '';

            $image_name = $filename .".". $extension;
            // $image_name = $filename;
            $path = 'uploaded_images/' . $image_name;

            //original
            if (!file_exists(public_path('uploaded_images' .  DIRECTORY_SEPARATOR) . $image_name)) {
                $img->save('uploaded_images/' . $image_name);
            }

            $image = $question->image()->create([
                'image' => $image_name,
                'image_path' => $path
            ]);

        }

        return response()->json([
            "success"=>true,
        ], 200);
    }

    public function destroy($id)
    {
        $question = Question::where('id', $id)->with('image')->first();
        Solution::where("question_id", $question->id)->delete();
        Answer::where("question_id", $question->id)->delete();

        $image = $question->image;
        if($image){
            $image_name = $image->image;
            unlink('uploaded_images/' . $image_name);
            Image::where("imageable_id", $question->id)->where("imageable_type", "App\Models\Question")->delete();
        }

        $question = Question::destroy($id);

        return response()->json([
            "success"=>true,
        ], 200);
    }

    public function all_questions() {
        $quiz = Question::with(['grade', 'subject', 'topic', 'subtopic', 'answers', 'solution', 'image', 'user'])->get();
        return response()->json($quiz);
    }
    public function total_questions() {
        $que = Question::get()->count();
        return response()->json($que);
    }
    public function total_tests() {
        $tests = Test::get()->count();
        return response()->json($tests);
    }
    public function today_quiz() {
        $que = Question::where('created_at', Carbon::today())->get()->count();
        if($que <= 0) {
            return response()->json("0");
        }
        return response()->json($que);
    }
    public function week_quiz() {
        $que = Question::whereDate('created_at', '>=', Carbon::today()->subDays(7))->get()->count();
         if($que <= 0) {
            return response()->json("0");
        }
        return response()->json($que);
    }
    public function month_quiz() {
        $que = Question::whereBetween('created_at', [Carbon::today()->firstOfMonth(),Carbon::today()->endOfMonth()])->get()->count();
         if($que <= 0) {
            return response()->json("0");
        }
        return response()->json($que);
    }
}

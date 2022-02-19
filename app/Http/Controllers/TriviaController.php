<?php

namespace App\Http\Controllers;

use App\Trivia;
use App\TriviaCategory;
use App\TriviaQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TriviaController extends Controller
{
    public function getAllTrivias()
    {
        $trivias = Trivia::with('triviaCategory')->withCount('triviaQuestions')->get();

        return pozzy_httpOk($trivias);
    }

    public function getTriviaQuestions($id)
    {
        $questions = Trivia::with('triviaQuestions')->with('triviaCategory')->where('id', $id)->get();

        return pozzy_httpOk($questions);
    }

    public function getTriviaCategories()
    {
        $categories = TriviaCategory::all();

        return pozzy_httpOk($categories);
    }

    public function getCategoryTriviaQuestions($id)
    {
        $trivias = Trivia::with('triviaQuestions')->where('trivia_category_id', $id)->get();

        return pozzy_httpOk($trivias);
    }

    public function addTriviaCategory(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'image' => 'required|mimes:jpg,jpeg,png'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $trivia = TriviaCategory::create([
            'name' => $request->name,
            'imagePath' => config('services.app_url.url').'/storage/trivia/category/'.pathinfo($request->image->store('category', 'trivia'), PATHINFO_BASENAME)
        ]);

        return pozzy_httpCreated($trivia);
    }

    public function addTrivia(Request $request)
    {
        $rules = [
            'category_id' => ['required'],
            'title' => ['required'],
            'description' => ['required'],
            'image' => ['required', 'mimes:jpg,png,jpeg']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $trivia = Trivia::create([
            'trivia_category_id' => $request->category_id,
            'title' => $request->title,
            'description' => strip_tags($request->description),
            'imagePath' => config('services.app_url.url').'/storage/trivia/trivia/'.pathinfo($request->image->store('trivia', 'trivia'), PATHINFO_BASENAME)
        ]);

        $trivia->load('triviaCategory')->loadCount('triviaQuestions');

        return pozzy_httpCreated($trivia);
    }

    public function updateTrivia(Request $request)
    {
        $rules = [
            'trivia_id' => ['required'],
            'category_id' => ['required'],
            'title' => ['required'],
            'description' => ['required'],
        ];

        $validator = Validator::make($request->all(),$rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $trivia = Trivia::find($request->trivia_id);
        $trivia->title = $request->title;
        $trivia->description = strip_tags($request->description);
        $trivia->trivia_category_id = $request->category_id;

        if ($request->hasFile('image')) {
            $trivia->imagePath = config('services.app_url.url').'/storage/trivia/trivia/'.pathinfo($request->image->store('trivia', 'trivia'), PATHINFO_BASENAME);
        }

        $trivia->save();

        $trivia->load('triviaCategory')->load('triviaQuestions');

        return pozzy_httpOk($trivia);
    }

    public function deleteTrivia($id)
    {
        $trivia = Trivia::find($id);
        $trivia->load('triviaQuestions');

        // $trivia->triviaQuestions->each(fn (TriviaQuestion $question) => $question->delete());
        foreach ($trivia->triviaQuestions as $question) {
            $question->delete();
        }

        $trivia->delete();

        return pozzy_httpOk($trivia);
    }

    public function addTriviaQuestions(Request $request)
    {
        $question = new TriviaQuestion;
        $question->trivia_id = $request->trivia_id;
        $question->text = $request->text;
        $question->duration = $request->duration;
        $optionsArray = [];
        for ($i=0; $i < count($request->options); $i++) {
            if ($request->correct === $request->options[$i]) {
                array_push($optionsArray, [$request->options[$i] => true]);
            } else {
                array_push($optionsArray, [$request->options[$i] => false]);
            }
        }
        $question->options = $optionsArray;
        $question->save();
        return pozzy_httpCreated($question);
    }

    public function deleteTriviaQuestion($id)
    {
        $question = TriviaQuestion::destroy($id);

        return pozzy_httpOk($question);
    }

}

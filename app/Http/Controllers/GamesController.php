<?php

namespace App\Http\Controllers;

use App\SpotDifference;
use App\Trivia;
use App\TriviaCategory;
use App\TriviaQuestion;
use App\TwoPicsGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GamesController extends Controller
{
    private function deleteFile($filePath, $folder)
    {
        $file = collect(explode('/', $filePath));
        Storage::disk('games')->delete($folder.'/'.$file->last());
    }

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
            'imagePath' => config('services.app_url.url').'/storage/games/trivia/category/'.pathinfo($request->image->store('trivia/category', 'games'), PATHINFO_BASENAME)
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
            'imagePath' => config('services.app_url.url').'/storage/games/trivia/trivia/'.pathinfo($request->image->store('trivia/trivia', 'games'), PATHINFO_BASENAME)
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
            $this->deleteFile($trivia->imagePath, 'trivia/trivia');
            $trivia->imagePath = config('services.app_url.url').'/storage/games/trivia/trivia/'.pathinfo($request->image->store('trivia/trivia', 'games'), PATHINFO_BASENAME);
        }

        $trivia->save();

        $trivia->load('triviaCategory')->load('triviaQuestions');

        return pozzy_httpOk($trivia);
    }

    public function deleteTrivia($id)
    {
        $trivia = Trivia::find($id);
        $trivia->load('triviaQuestions');

        collect($trivia->triviaQuestions)->each(fn (TriviaQuestion $question) => $question->delete());

        $this->deleteFile($trivia->imagePath, 'trivia/trivia');

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

    public function getPicsGames()
    {
        $picsGames = TwoPicsGame::all();

        return pozzy_httpOk($picsGames);
    }

    public function addPicsGame(Request $request)
    {
        $rules = [
            'image_one' => ['required', 'mimes:jpg,png,jpeg'],
            'image_two' => ['required', 'mimes:jpg,png,jpeg'],
            'answer' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $twoPics = new TwoPicsGame;

        $twoPics->answer = $request->answer;
        $hintCases = collect(['', null, 'Not Applicable', 'not applicable' , 'Not applicable' ,'N/A', 'n/a']);
        if ($hintCases->contains($request->hint)) {
            $twoPics->hint = null;
        } else {
            $twoPics->hint = $request->hint;
        }

        $twoPics->duration = $request->duration;

        $twoPics->pictureOne = config('services.app_url.url').'/storage/games/twopics/'.pathinfo($request->image_one->store('twopics', 'games'), PATHINFO_BASENAME);
        $twoPics->pictureTwo = config('services.app_url.url').'/storage/games/twopics/'.pathinfo($request->image_two->store('twopics', 'games'), PATHINFO_BASENAME);

        $twoPics->save();

        return pozzy_httpCreated($twoPics);
    }

    public function deleteTwoPicsGame($id)
    {
        $deleted = TwoPicsGame::find($id);

        $this->deleteFile($deleted->pictureOne, 'twopics');
        $this->deleteFile($deleted->pictureTwo, 'twopics');

        $deleted->delete();

        return pozzy_httpOk($deleted);
    }

    public function updateTwoPicsGame(Request $request)
    {
        $rules = [
            'answer' => ['required']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $game = TwoPicsGame::find($request->game_id);


        if ($request->hint != '' || $request->hint != null) {
            $game->hint = $request->hint;
        }

        $game->duration = $request->duration;

        if ($request->hasFile('image_one')) {
            $this->deleteFile($game->pictureOne, 'twopics');

            $game->pictureOne = config('services.app_url.url').'/storage/games/twopics/'.pathinfo($request->image_one->store('twopics', 'games'), PATHINFO_BASENAME);
        }

        if ($request->hasFile('image_two')) {
            $this->deleteFile($game->pictureTwo, 'twopics');

            $game->pictureTwo = config('services.app_url.url').'/storage/games/twopics/'.pathinfo($request->image_two->store('twopics', 'games'), PATHINFO_BASENAME);
        }

        $game->save();

        return pozzy_httpOk($game);
    }

    public function getNewPicGame()
    {
        $newGame = null;
        // Get all games
        $allGames = TwoPicsGame::inRandomOrder()->get();
        // Check if user has played the game
        foreach ($allGames as $game) {
            if (!$game->userHasPlayed(auth()->user())) {
                $newGame = $game;
            }
        }

        if ($newGame != null) {
            return pozzy_httpOk($newGame);
        } else {
            return pozzy_httpOk('No new game found');
        }
    }

    public function saveSolvedPicGame(Request $request)
    {
        $rules = [
            'game_id' => ['required']
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::table('user_two_pics_games')->insert([
            'user_id' => auth()->user()->id,
            'two_pics_game_id' => $request->game_id
        ]);

        return pozzy_httpOk('Game saved');
    }

    public function getSpotDifferenceGames()
    {
        $games = SpotDifference::all();

        return pozzy_httpOk($games);
    }

    public function addSpotDifferenceGame(Request $request)
    {
        $rules = [
            'image_one' => ['required', 'mimes:jpg,png,jpeg'],
            'image_two' => ['required', 'mimes:jpg,png,jpeg'],
            'differences' => ['required']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $game = new SpotDifference;

        $game->differences = collect(explode(',', strip_tags($request->differences)))->map(fn ($difference) => trim($difference));

        if ($request->duration != '' || $request->duration != null) {
            $game->duration = $request->duration;
        }

        $game->firstImagePath = config('services.app_url.url').'/storage/games/spotdifference/'.pathinfo($request->image_one->store('spotdifference', 'games'), PATHINFO_BASENAME);
        $game->secondImagePath = config('services.app_url.url').'/storage/games/spotdifference/'.pathinfo($request->image_two->store('spotdifference', 'games'), PATHINFO_BASENAME);

        $game->save();

        return pozzy_httpOk($game);
    }

    public function updateSpotDifferenceGame(Request $request)
    {
        $rules = [
            'differences' => ['required']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $game = SpotDifference::find($request->game_id);

        $game->differences = collect(explode(',', strip_tags($request->differences)))->map(fn ($difference) => trim($difference));

        if ($request->duration != '' || $request->duration != null) {
            $game->duration = $request->duration;
        }

        if ($request->hasFile('image_one')) {
            $this->deleteFile($game->firstImagePath, 'spotdifference');

            $game->firstImagePath = config('services.app_url.url').'/storage/games/spotdifference/'.pathinfo($request->image_one->store('spotdifference', 'games'), PATHINFO_BASENAME);
        }

        if ($request->hasFile('image_two')) {
            $this->deleteFile($game->secondImagePath, 'spotdifference');

            $game->secondImagePath = config('services.app_url.url').'/storage/games/spotdifference/'.pathinfo($request->image_two->store('spotdifference', 'games'), PATHINFO_BASENAME);
        }

        $game->save();

        return pozzy_httpOk($game);
    }

    public function deleteSpoDifferenceGame($id)
    {
        $deleted = SpotDifference::find($id);

        $this->deleteFile($deleted->firstImagePath, 'spotdifference');
        $this->deleteFile($deleted->secondImagePath, 'spotdifference');

        $deleted->delete();

        return pozzy_httpOk($deleted);
    }

    public function getNewSpotDifferenceGame()
    {
        $newGame = null;
        // Get all games
        $allGames = SpotDifference::inRandomOrder()->get();
        // Check if user has played the game
        foreach ($allGames as $game) {
            if (!$game->userHasPlayed(auth()->user())) {
                $newGame = $game;
            }
        }

        if ($newGame != null) {
            return pozzy_httpOk($newGame);
        } else {
            return pozzy_httpOk('No new game found');
        }
    }

    public function saveSpotDifferenceResponse(Request $request)
    {
        $rules = [
            'game_id' => ['required'],
            'differences' => ['required'],
            'duration' => ['required']
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::table('user_two_pics_games')->insert([
            'user_id' => auth()->user()->id,
            'spot_differences_id' => $request->game_id
        ]);

        return pozzy_httpOk('Game saved');
    }
}

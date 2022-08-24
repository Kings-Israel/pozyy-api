<?php

namespace App\Http\Controllers;

use App\GamesLeaderboard;
use App\Kid;
use App\School;
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
            'age_group' => ['required'],
            'title' => ['required'],
            'description' => ['required'],
            'image' => ['required', 'mimes:jpg,png,jpeg'],
            'start_time' => ['required']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $trivia = Trivia::create([
            'trivia_category_id' => $request->category_id,
            'age_group' => $request->age_group,
            'title' => $request->title,
            'description' => strip_tags($request->description),
            'imagePath' => config('services.app_url.url').'/storage/games/trivia/trivia/'.pathinfo($request->image->store('trivia/trivia', 'games'), PATHINFO_BASENAME),
            'start_time' => $request->start_time,
        ]);

        $trivia->load('triviaCategory')->loadCount('triviaQuestions');

        return pozzy_httpCreated($trivia);
    }

    public function updateTrivia(Request $request)
    {
        $rules = [
            'trivia_id' => ['required'],
            'category_id' => ['required'],
            'age_group' => ['required'],
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
        $trivia->age_group = $request->age_group;
        $trivia->start_time = $request->start_time;

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
                array_push($optionsArray, ['text' => $request->options[$i], 'isCorrect' => true]);
            } else {
                array_push($optionsArray, ['text' => $request->options[$i], 'isCorrect' => false]);
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

    public function getNewTriviaQuestion($id)
    {
        $newGame = null;
        // Get all games
        $allGames = TriviaQuestion::inRandomOrder()->where('trivia_id', $id)->get();
        // Check if user has played the game
        foreach ($allGames as $game) {
            if (!$game->userHasPlayed(auth()->user())) {
                $newGame = $game;
            }
        }

        if ($newGame != null) {
            DB::table('users_games_played')->insert(
                ['user_id' => auth()->user()->id,
                'trivia_id' => $newGame->id]
            );
            return pozzy_httpOk($newGame);
        } else {
            return pozzy_httpOk('No new game found');
        }

    }

    public function saveSolvedTriviaQuestion(Request $request)
    {
        $rules = [
            'kid_id' => ['required'],
            'question_id' => ['required'],
            'answer' => ['required'],
            'duration' => ['required']
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $question = TriviaQuestion::find($request->question_id);
        $correctAnswer = '';

        for ($i=0; $i < count($question->options); $i++) {
            if ($question->options[$i][key($question->options[$i])] === true) {
                $correctAnswer = key($question->options[$i]);
            }
        }

        if (strtolower($request->answer) != strtolower($correctAnswer)) {
            return pozzy_httpBadRequest('The answer submitted was not correct');
        }

        $leaderboard = GamesLeaderboard::firstOrNew(['user_id' => $request->kid_id]);
        $leaderboard->total_points += 5;
        $time = (int) $question->duration - (int) $request->duration;
        $leaderboard->total_time += $time;
        $leaderboard->save();

        return pozzy_httpOk('Game saved');
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
            'age_group' => ['required'],
            'start_time' => ['required']
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
        $twoPics->age_group = $request->age_group;
        $twoPics->start_time = $request->start_time;

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
            'answer' => ['required'],
            'age_group' => ['required'],
            'start_time' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $game = TwoPicsGame::find($request->game_id);

        $game->answer = $request->answer;
        $game->hint = $request->hint;
        $game->duration = $request->duration;
        $game->age_group = $request->age_group;
        $game->start_time = $request->start_time;

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
            DB::table('users_games_played')->insert(
                ['user_id' => auth()->user()->id,
                'two_pics_games_id' => $newGame->id]
            );
            return pozzy_httpOk($newGame);
        } else {
            return pozzy_httpOk('No new game found');
        }
    }

    public function saveSolvedPicGame(Request $request)
    {
        $rules = [
            'kid_id' => ['required'],
            'game_id' => ['required'],
            'answer' => ['required'],
            'duration' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $answer = TwoPicsGame::find($request->game_id);

        if (strtolower($answer->answer) != strtolower($request->answer)) {
            return pozzy_httpBadRequest('The answer is not correct');
        }

        $leaderboard = GamesLeaderboard::firstOrNew(['user_id' => $request->kid_id]);
        $leaderboard->total_points += 5;
        $time = (int) $answer->duration - (int) $request->duration;
        $leaderboard->total_time += $time;
        $leaderboard->save();

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
            'differences' => ['required'],
            'age_group' => ['required'],
            'start_time' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $game = new SpotDifference;

        $game->differences = collect(explode(',', strip_tags($request->differences)))->map(fn ($difference) => trim($difference));
        $game->age_group = $request->age_group;
        $game->start_time = $request->start_time;

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
            'differences' => ['required'],
            'age_group' => ['required'],
            'start_time' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $game = SpotDifference::find($request->game_id);

        $game->differences = collect(explode(',', strip_tags($request->differences)))->map(fn ($difference) => trim($difference));
        $game->age_group = $request->age_group;
        $game->start_time = $request->start_time;

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

        foreach ($allGames as $game) {
            // Check if user has NOT played the game
            if (!$game->userHasPlayed(auth()->user())) {
                $newGame = $game;
            }
        }

        if ($newGame != null) {
            // Add Game to Games played by user table
            DB::table('users_games_played')->insert(
                ['user_id' => auth()->user()->id,
                'spot_difference_id' => $newGame->id]
            );
            // Return game
            return pozzy_httpOk($newGame);
        } else {
            return pozzy_httpOk('No new game found');
        }
    }

    public function saveSpotDifferenceResponse(Request $request)
    {
        $rules = [
            'kid_id' => ['required'],
            'game_id' => ['required'],
            'differences' => ['required'],
            'duration' => ['required']
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Get the type of differences variable
        $differencesType = gettype($request->differences);

        // If differences is a string, convert to string
        $differences = [];
        if ($differencesType == 'string') {
            $differences = collect(explode(',', strip_tags($request->differences)))->map(fn ($difference) => strtolower(trim($difference)));
        } else {
            $differences = collect($request->differences)->map(fn ($difference) => strtolower($difference));
        }

        // Compare the response with the differences
        $answer = SpotDifference::find($request->game_id);
        $savedDifferences = collect($answer->differences)->map(fn ($difference) => strtolower(trim($difference)));
        // return response()->json($savedDifferences);
        $points = 0;
        collect($differences)->each(function ($difference) use ($savedDifferences, $points) {
            if($savedDifferences->contains($difference)) {
                $points += 5;
            }
        });

        $leaderboard = GamesLeaderboard::firstOrNew(['user_id' => $request->kid_id]);
        $leaderboard->total_points += 5;
        $time = (int) $answer->duration - (int) $request->duration;
        $leaderboard->total_time += $time;
        $leaderboard->save();

        return pozzy_httpOk('Game saved');
    }

    public function leaderboard()
    {
        $leaderboard = GamesLeaderboard::all();

        $leaderboard->each(function ($kid) {
            $kidDetails = Kid::find($kid->user_id);
            $kid['kid'] = $kidDetails->load('school');
        });

        return pozzy_httpOk($leaderboard);
    }

    public function school_leaderboard($id)
    {
        $kids = Kid::where('school_id', $id)->get();
        $kids->filter(function ($kid) {
            $kid->leaderboard()->exists();
        });

        return pozzy_httpOk($kids->load('leaderboard'));
    }
}

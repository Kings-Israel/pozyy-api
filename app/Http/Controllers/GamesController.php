<?php

namespace App\Http\Controllers;

use App\Kid;
use App\School;
use App\Trivia;
use App\GameNight;
use Carbon\Carbon;
use App\TwoPicsGame;
use App\UserGameNight;
use App\SpotDifference;
use App\TriviaCategory;
use App\TriviaQuestion;
use App\GamesLeaderboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class GamesController extends Controller
{
    private function deleteFile($filePath, $folder)
    {
        $file = collect(explode('/', $filePath));
        Storage::disk('games')->delete($folder.'/'.$file->last());
    }

    public function getAllTrivias()
    {
        $trivias = Trivia::with(['triviaCategory', 'gameNight' => fn($q) => $q->withTrashed()])->withCount('triviaQuestions')->get();

        return pozzy_httpOk($trivias);
    }

    public function getTriviaQuestions($id)
    {
        $questions = Trivia::with('triviaQuestions')->with('triviaCategory')->where('id', $id)->get();

        foreach ($questions as $trivia) {
            foreach ($trivia->triviaQuestions as $question) {
                $question['user_has_played'] = $question->userHasPlayed(auth()->user());
            }
        }

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

    public function deleteTriviaCategory($id)
    {
        $trivias = Trivia::where('trivia_category_id', $id)->get();

        $trivias->each(fn ($trivia) => $trivia->update(['trivia_category_id' => NULL]));

        TriviaCategory::destroy($id);

        return pozzy_httpOk('Trivia Category Deleted');
    }

    public function addTrivia(Request $request)
    {
        $rules = [
            // 'category_id' => ['required'],
            'age_group' => ['required'],
            'title' => ['required'],
            'description' => ['required'],
            'image' => ['required', 'mimes:jpg,png,jpeg'],
            'start_time' => ['required'],
            'end_time' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $trivia = Trivia::create([
            // 'trivia_category_id' => $request->category_id,
            'age_group' => $request->age_group,
            'title' => $request->title,
            'description' => strip_tags($request->description),
            'imagePath' => config('services.app_url.url').'/storage/games/trivia/trivia/'.pathinfo($request->image->store('trivia/trivia', 'games'), PATHINFO_BASENAME),
            'start_time' => $request->start_time,
            'end_time' => Carbon::parse($request->start_time)->addMinutes($request->end_time),
        ]);

        $trivia->load('triviaCategory')->loadCount('triviaQuestions')->load('gameNight');

        return pozzy_httpCreated($trivia);
    }

    public function updateTrivia(Request $request)
    {
        $rules = [
            'trivia_id' => ['required'],
            // 'category_id' => ['required'],
            // 'age_group' => ['required'],
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
        // $trivia->trivia_category_id = $request->category_id;
        // $trivia->age_group = $request->age_group;
        $trivia->start_time = $request->start_time;
        $trivia->end_time = Carbon::parse($request->start_time)->addMinutes($request->end_time);

        if ($request->hasFile('image')) {
            $this->deleteFile($trivia->imagePath, 'trivia/trivia');
            $trivia->imagePath = config('services.app_url.url').'/storage/games/trivia/trivia/'.pathinfo($request->image->store('trivia/trivia', 'games'), PATHINFO_BASENAME);
        }

        $trivia->save();

        $trivia->load('triviaCategory')->load('triviaQuestions')->load('gameNight');

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

        $user_has_played = DB::table('users_games_played')->where([
                                'user_id' => auth()->id(),
                                'trivia_id' => $request->question_id
                            ])->first();

        if ($user_has_played) {
            return pozzy_httpForbidden('You have already played this game');
        }

        $question = TriviaQuestion::with('trivia.gameNight')->find($request->question_id);

        $correctAnswer = '';

        for ($i=0; $i < count($question->options); $i++) {
            if ($question->options[$i]['isCorrect'] === true) {
                $correctAnswer = $question->options[$i][key($question->options[$i])];
            }
        }

        $points = 0;

        if (strtolower($request->answer) === strtolower($correctAnswer)) {
            $points = 5;
        }

        $kid = Kid::with('parent')->find($request->kid_id);

        $game_night_id = null;

        if($question->trivia->gameNight) {
            $game_night_id = $question->trivia->gameNight->id;
        }

        GamesLeaderboard::create([
            'user_id' => auth()->id(),
            'kid_id' => $kid->id,
            'total_points' => $points,
            'total_time' => (int) $question->duration - (int) $request->duration,
            'gameable_id' => $question->id,
            'gameable_type' => TriviaQuestion::class,
            'game_night_id' => $game_night_id,
        ]);

        DB::table('users_games_played')->insert([
            'user_id' => auth()->id(),
            'trivia_id' => $question->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return pozzy_httpOk('Game saved');
    }

    public function getPicsGames()
    {
        $picsGames = TwoPicsGame::with('gameNight')->get();

        foreach ($picsGames as $game) {
            $question['user_has_played'] = $game->userHasPlayed(auth()->user());
        }

        return pozzy_httpOk($picsGames);
    }

    public function addPicsGame(Request $request)
    {
        $rules = [
            'image_one' => ['required', 'mimes:jpg,png,jpeg'],
            'image_two' => ['required', 'mimes:jpg,png,jpeg'],
            'answer' => ['required'],
            'age_group' => ['required'],
            'start_time' => ['required'],
            'end_time' => ['required'],
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
        $twoPics->start_time = $request->start_time;
        $twoPics->end_time = Carbon::parse($request->start_time)->addMinutes($request->end_time);

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
            'end_time' => ['required'],
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
        $game->end_time = Carbon::parse($request->start_time)->addMinutes($request->end_time);

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
            'kid_id' => ['required'],
            'game_id' => ['required'],
            'answer' => ['required'],
            'duration' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user_has_played = DB::table('users_games_played')->where([
                    'user_id' => auth()->id(),
                    'two_pics_games_id' => $request->game_id
                ])->first();

        if ($user_has_played) {
            return pozzy_httpForbidden('You have already played this game');
        }

        $answer = TwoPicsGame::find($request->game_id);

        $points = 0;

        if (strtolower($answer->answer) === strtolower($request->answer)) {
            $points = 5;
        }

        // $leaderboard = GamesLeaderboard::firstOrNew(['user_id' => $request->kid_id]);
        // $leaderboard->total_points += 5;
        // $time = (int) $answer->duration - (int) $request->duration;
        // $leaderboard->total_time += $time;
        // $leaderboard->save();

        $kid = Kid::with('parent')->find($request->kid_id);

        $game_night_id = null;

        if($answer->gameNight) {
            $game_night_id = $answer->gameNight->id;
        }

        GamesLeaderboard::create([
            'user_id' => auth()->id(),
            'kid_id' => $kid->id,
            'total_points' => $points,
            'total_time' => (int) $answer->duration - (int) $request->duration,
            'gameable_id' => $answer->id,
            'gameable_type' => TwoPicsGame::class,
            'game_night_id' => $game_night_id,
        ]);

        DB::table('users_games_played')->insert([
            'user_id' => auth()->id(),
            'two_pics_games_id' => $answer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return pozzy_httpOk('Game saved');
    }

    public function getSpotDifferenceGames()
    {
        $games = SpotDifference::with('gameNight')->get();

        foreach ($games as $game) {
            $question['user_has_played'] = $game->userHasPlayed(auth()->user());
        }

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
            'end_time' => ['required']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $game = new SpotDifference;

        $game->differences = collect(explode(',', strip_tags($request->differences)))->map(fn ($difference) => trim($difference));
        $game->age_group = $request->age_group;
        $game->start_time = $request->start_time;
        $game->end_time = Carbon::parse($request->start_time)->addMinutes($request->end_time);

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
            'end_time' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $game = SpotDifference::find($request->game_id);

        $game->differences = collect(explode(',', strip_tags($request->differences)))->map(fn ($difference) => trim($difference));
        $game->age_group = $request->age_group;
        $game->start_time = $request->start_time;
        $game->end_time = Carbon::parse($request->start_time)->addMinutes($request->end_time);

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

        $user_has_played = DB::table('users_games_played')->where([
                    'user_id' => auth()->id(),
                    'spot_difference_id' => $request->game_id
                ])->first();

        if ($user_has_played) {
            return pozzy_httpForbidden('You have already played this game');
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

        $points = 0;
        collect($differences)->each(function ($difference) use ($savedDifferences, $points) {
            if($savedDifferences->contains($difference)) {
                $points += 5;
            }
        });

        // $leaderboard = GamesLeaderboard::firstOrNew(['user_id' => $request->kid_id]);
        // $leaderboard->total_points += 5;
        // $time = (int) $answer->duration - (int) $request->duration;
        // $leaderboard->total_time += $time;
        // $leaderboard->save();

        $kid = Kid::with('parent')->find($request->kid_id);

        $game_night_id = null;

        if($answer->gameNight) {
            $game_night_id = $answer->gameNight->id;
        }

        GamesLeaderboard::create([
            'user_id' => auth()->id(),
            'kid_id' => $kid->id,
            'total_points' => (int) $points,
            'total_time' => (int) $answer->duration - (int) $request->duration,
            'gameable_id' => $answer->id,
            'gameable_type' => SpotDifference::class,
            'game_night_id' => $game_night_id,
        ]);

        DB::table('users_games_played')->insert([
            'user_id' => auth()->user()->id,
            'spot_difference_id' => $answer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return pozzy_httpOk('Game saved');
    }

    public function leaderboard()
    {
        $kids = [];
        if (auth()->user()->getRoleNames()[0] === 'admin') {
            // Get Latest Game Night
            $game_night = GameNight::latest()->first();

            $leaderboard = GamesLeaderboard::where('game_night_id', $game_night->id)->get()->unique('user_id');
            foreach ($leaderboard as $board) {
                $kidDetails = User::with('kids.school')->find($board->kid->parent_id);
                $kidDetails['total_points'] = GamesLeaderboard::where('user_id', $board->user_id)->where('game_night_id', $game_night->id)->sum('total_points');
                $kidDetails['total_time'] = GamesLeaderboard::where('user_id', $board->user_id)->where('game_night_id', $game_night->id)->sum('total_time');
                array_push($kids, $kidDetails);
                $kids = array_values(Arr::sort($kids, function($value) {
                    return !$value['total_points'];
                }));
            }
        } else {
            // Get Latest Game Night
            $game_night = GameNight::latest()->first();

            if ($game_night) {
                $leaderboard = GamesLeaderboard::where('game_night_id', $game_night->id)->get()->unique('user_id');
                foreach ($leaderboard as $board) {
                    $kidDetails = User::with('kids.school')->find($board->kid->parent_id);
                    $kidDetails['total_points'] = (string) GamesLeaderboard::where('user_id', $board->user_id)->where('game_night_id', $game_night->id)->sum('total_points');
                    $kidDetails['total_time'] = (string) GamesLeaderboard::where('user_id', $board->user_id)->where('game_night_id', $game_night->id)->sum('total_time');
                    array_push($kids, $kidDetails);
                }
            }
        }

        return pozzy_httpOk($kids);
    }

    public function school_leaderboard($id)
    {
        // Get Latest Game Night
        $game_night = GameNight::latest()->withTrashed()->first();

        $leaderboard = GamesLeaderboard::with('kid')->whereHas(
                        'kid', function($query) use ($id) {
                                $query->where('school_id', $id);
                            }
                        )
                        ->where('game_night_id', $game_night->id)
                        ->get()
                        ->unique('kid_id');

        $kids = [];
        foreach ($leaderboard as $kid) {
            $kidDetails = Kid::with('school')->find($kid->kid_id);
            $kidDetails['total_points'] = GamesLeaderboard::where('game_night_id', $game_night->id)->where('kid_id', $kid->id)->sum('total_points');
            $kidDetails['total_time'] = GamesLeaderboard::where('game_night_id', $game_night->id)->where('kid_id', $kid->id)->sum('total_time');
            array_push($kids, $kidDetails);
            $kids = array_values(Arr::sort($kids, function($value) {
                return !$value['total_points'];
            }));
        }

        $user_game_nights = null;
        $school = School::with('admin')->find(auth()->user()->school_id);

        if ($school->users->count() > 0) {
            // Get School Users apart from school admin
            $users = $school->users->filter(function ($user) {
                return $user->email !== auth()->user()->email;
            })->pluck('id');

            // Get game nights where there's a student from the school registered in
            // $user_game_nights = UserGameNight::withCount('user')->with('gameNight')->whereIn('user_id', $users)->get();
            $user_game_nights = UserGameNight::withCount([
                                                        'user' => function($query) {
                                                            $query->withTrashed();
                                                        }])
                                            ->with(['gameNight' => function($query) {
                                                $query->withTrashed();
                                            }])
                                            ->whereIn('user_id', $users)
                                            ->get()
                                            ->groupBy('game_night_id');
        }

        return pozzy_httpOk(['leaderboard' => $kids, 'game_night_data' => $user_game_nights]);
    }

    public function addToGameNight(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game_type' => 'required',
            'game_id' => 'required',
            'game_night_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $game_night = GameNight::find($request->game_night_id);
        $games = NULL;

        switch ($request->game_type) {
            case 'Trivia':
                $trivia = Trivia::find($request->game_id);
                $duration = Carbon::parse($trivia->start_time)->diffInMinutes(Carbon::parse($trivia->end_time));
                $trivia->update([
                    'game_night_id' => $request->game_night_id,
                ]);
                $games = Trivia::with(['triviaCategory', 'gameNight'])->get();
                break;
            case 'Spot Difference':
                $spot_difference = SpotDifference::find($request->game_id);
                $duration = Carbon::parse($spot_difference->start_time)->diffInMinutes(Carbon::parse($spot_difference->end_time));
                $spot_difference->update([
                    'game_night_id' => $request->game_night_id,
                ]);
                $games = SpotDifference::with('gameNight')->get();
                break;
            case 'Two Pics Game':
                $two_pics = TwoPicsGame::find($request->game_id);
                $duration = Carbon::parse($two_pics->start_time)->diffInMinutes(Carbon::parse($two_pics->end_time));
                $two_pics->update([
                    'game_night_id' => $request->game_night_id,
                ]);
                $games = TwoPicsGame::with('gameNight')->get();
                break;

            default:
                return pozzy_httpNotFound('Game Not Found');
                break;
        }

        $game_night->update([
            'duration' => $game_night->duration + $duration,
        ]);

        return response()->json(['message' => 'Game added to game night', 'data' => $games], 200);
    }

    public function removeFromGameNight(Request $request)
    {
        $game_night = GameNight::find($request->game_night_id);
        $games = NULL;

        switch ($request->game_type) {
            case 'Trivia':
                $trivia = Trivia::find($request->game_id);
                $duration = Carbon::parse($trivia->start_time)->diffInMinutes(Carbon::parse($trivia->end_time));
                $games = Trivia::all();
                $trivia->update([
                    'game_night_id' => NULL,
                ]);
                break;
            case 'Spot Difference':
                $spot_difference = SpotDifference::find($request->game_id);
                $duration = Carbon::parse($spot_difference->start_time)->diffInMinutes(Carbon::parse($spot_difference->end_time));
                $games = SpotDifference::all();
                $spot_difference->update([
                    'game_night_id' => NULL,
                ]);
                break;
            case 'Two Pics':
                $two_pics = TwoPicsGame::find($request->game_id);
                $duration = Carbon::parse($two_pics->start_time)->diffInMinutes(Carbon::parse($two_pics->end_time));
                $games = TwoPicsGame::all();
                $two_pics->update([
                    'game_night_id' => NULL,
                ]);
                break;

            default:
                return pozzy_httpNotFound('Game Not Found');
                break;
        }

        $game_night->update([
            'duration' => $game_night->duration - $duration,
        ]);

        return response()->json(['message' => 'Game removed from game night', 'data' => $games], 200);
    }
}

<?php

namespace App\Http\Controllers;

use Image;
use App\Trivia;
use App\GameNight;
use Carbon\Carbon;
use App\TwoPicsGame;
use App\MpesaPayment;
use App\UserGameNight;
use App\SpotDifference;
use App\GameNightCategory;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MpesaPaymentController;

class GameNightController extends Controller
{
    public function index()
    {
        $game_nights = GameNight::with([
                                    'triviaGames.triviaQuestions',
                                    'twoPicsGames',
                                    'spotDifferencesGames',
                                    'category'
                                ])
                                ->withCount([
                                    'payments' => function($query) {
                                        $query->where('mpesa_receipt_number', '!=', null);
                                    }
                                ])
                                ->get();

        return pozzy_httpOk($game_nights);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'price' => 'required',
            'category_id' => 'required',
            'poster' => 'required|mimes:png,jpg,jpeg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $image = $request->file('poster');
        $input['imagename'] = time().'.'.$image->extension();

        $filePath = public_path('storage/game-night/poster');
        $img = Image::make($image->path());
        $img->resize(700, 464, function($const) {
            $const->aspectRatio();
        })->save($filePath.'/'.$input['imagename']);

        $game_night = GameNight::create([
            'title' => $request->title,
            'start_date' => $request->start_date,
            'start_time' => $request->start_time,
            'price' => $request->price,
            'poster' => config('app.url').'/storage/game-night/poster/'.$img->basename,
            'category_id' => $request->category_id
        ]);

        return response()->json(['message' => 'Game Night added successfully', 'data' => $game_night->load('category')], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'price' => 'required',
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        if ($request->hasFile('poster')) {
            $image = $request->file('poster');
            $input['imagename'] = time().'.'.$image->extension();

            $filePath = public_path('storage/game-night/poster');
            $img = Image::make($image->path());
            $img->resize(700, 464, function($const) {
                $const->aspectRatio();
            })->save($filePath.'/'.$input['imagename']);
        }

        $game_night = GameNight::find($id);

        $game_night->update([
            'title' => $request->title,
            'start_date' => $request->start_date,
            'start_time' => $request->start_time,
            'price' => $request->price,
            'poster' => $request->hasFile('poster') ? config('app.url').'/storage/game-night/poster/'.$img->basename : $game_night->poster,
            'category_id' => $request->category_id,
        ]);

        $game_nights = GameNight::with('triviaGames', 'twoPicsGames', 'spotDifferencesGames', 'category')->get();

        return response()->json(['message' => 'Game Night updated', 'data' => $game_nights]);
    }

    public function destroy($id)
    {
        $game_night = GameNight::find($id);

        $trivias = Trivia::where('game_night_id', $id)->get();
        $trivias->each(fn ($trivia) => $trivia->update(['game_night_id' => NULL]));

        $two_pics_games = TwoPicsGame::where('game_night_id',$id)->get();
        $two_pics_games->each(fn ($game) => $game->update(['game_night_id' => NULL]));

        $spot_differences = SpotDifference::where('game_night_id', $id)->get();
        $spot_differences->each(fn ($spot_difference) => $spot_difference->update(['game_night_id' => NULL]));

        Storage::disk('game-night')->delete('poster/'.$game_night->poster);

        $game_night->delete();

        return response()->json(['message' => 'Game Night Delete successfully', 'data' => $game_night], 200);
    }

    public function getGameNights()
    {
        $game_nights = GameNight::with('triviaGames.triviaQuestions', 'twoPicsGames', 'spotDifferencesGames', 'category')->get();

        foreach ($game_nights as $key => $game_night) {
            if ($game_night->triviaGames) {
                foreach ($game_night->triviaGames as $trivia_game) {
                    foreach ($trivia_game->triviaQuestions as $question) {
                        $question['user_has_played'] = $question->userHasPlayed(auth()->user());
                    }
                }
            }
            if ($game_night->twoPicsGames) {
                foreach ($game_night->twoPicsGames as $game) {
                    $game['user_has_played'] = $game->userHasPlayed(auth()->user());
                }
            }
            if ($game_night->spotDifferencesGames) {
                foreach ($game_night->spotDifferencesGames as $game) {
                    $game['user_has_played'] = $game->userHasPlayed(auth()->user());
                }
            }

            if ($game_night->userCanPlay()) {
                $game_night['can_play'] = true;
            } else {
                $game_night['can_play'] = false;
            }
        }

        return response()->json(['message' => '', 'data' => $game_nights], 200);
    }

    public function getGameNightGames($id)
    {
        $game_night = GameNight::with('triviaGames', 'twoPicsGames', 'spotDifferencesGames', 'category')->where('id', $id)->first();

        if ($game_night->triviaGames) {
            foreach ($game_night->triviaGames as $trivia_game) {
                foreach ($trivia_game->triviaQuestions as $question) {
                    $question['user_has_played'] = $question->userHasPlayed(auth()->user());
                }
            }
        }
        if ($game_night->twoPicsGames) {
            foreach ($game_night->twoPicsGames as $game) {
                $game['user_has_played'] = $game->userHasPlayed(auth()->user());
            }
        }
        if ($game_night->spotDifferencesGames) {
            foreach ($game_night->spotDifferencesGames as $game) {
                $game['user_has_played'] = $game->userHasPlayed(auth()->user());
            }
        }

        if ($game_night->userCanPlay()) {
            $game_night['can_play'] = true;
        } else {
            $game_night['can_play'] = false;
        }

        return response()->json(['message' => '', 'data' => $game_night], 200);
    }

    public function gameNightPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game_night_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $game_night = GameNight::find($request->game_night_id);

        if(!$game_night) {
            return response()->json(['message' => 'Game night not found'], 400);
        }

        if ($game_night->userCanPlay()) {
            return response()->json(['message' => 'User already paid for game night'], 200);
        }

        $phone_number = Auth::user()->phone_number;
        if (strlen($request->phone_number) == 9) {
            $phone_number = '254'.$phone_number;
        } else {
            $phone_number = '254'.substr($phone_number, -9);
        }

        $account_number = Str::upper(Str::random(3)).time().Str::upper(Str::random(3));

        $transaction = new MpesaPaymentController;
        $results = $transaction->stkPush(
            $phone_number,
            $game_night->price,
            // '1',
            route('game-night.payment.callback'),
            $account_number,
            'Game Night Payment'
        );

        if ($results['response_code'] == 0) {
            $mpesa_payable_type = GameNight::class;
            MpesaPayment::create([
                'user_id' => Auth::user()->id,
                'user_phone_number' => $phone_number,
                'mpesa_payable_id' => $game_night->id,
                'mpesa_payable_type' => $mpesa_payable_type,
                'checkout_request_id' => $results['checkout_request_id']
            ]);

            return pozzy_httpOk('Payment is being processed');
        }

        return pozzy_httpBadRequest('Something went wrong');
    }

    public function gameNightPaymentCallback(Request $request)
    {
        $callbackJSONData = file_get_contents('php://input');
        $callbackData = json_decode($callbackJSONData);

        info($callbackJSONData);

        $result_code = $callbackData->Body->stkCallback->ResultCode;
        $merchant_request_id = $callbackData->Body->stkCallback->MerchantRequestID;
        $checkout_request_id = $callbackData->Body->stkCallback->CheckoutRequestID;
        $amount = $callbackData->Body->stkCallback->CallbackMetadata->Item[0]->Value;
        $mpesa_receipt_number = $callbackData->Body->stkCallback->CallbackMetadata->Item[1]->Value;

        if($result_code === 0) {
            $mpesaPayment = MpesaPayment::where('checkout_request_id', $checkout_request_id)->first();
            $mpesaPayment->mpesa_receipt_number = $mpesa_receipt_number;
            $mpesaPayment->save();

            UserGameNight::create([
                'user_id' => $mpesaPayment->user_id,
                'game_night_id' => $mpesaPayment->mpesa_payable_id
            ]);
        }
    }

    public function getCategories()
    {
        $categories = GameNightCategory::all();

        return response()->json(['data' => $categories]);
    }

    public function getGameDays()
    {
        $game_nights = GameNight::with('triviaGames.triviaQuestions', 'twoPicsGames', 'spotDifferencesGames', 'category')->where('category_id', 1)->get();

        foreach ($game_nights as $key => $game_night) {
            if ($game_night->triviaGames) {
                foreach ($game_night->triviaGames as $trivia_game) {
                    foreach ($trivia_game->triviaQuestions as $question) {
                        $question['user_has_played'] = $question->userHasPlayed(auth()->user());
                    }
                }
            }
            if ($game_night->twoPicsGames) {
                foreach ($game_night->twoPicsGames as $game) {
                    $game['user_has_played'] = $game->userHasPlayed(auth()->user());
                }
            }
            if ($game_night->spotDifferencesGames) {
                foreach ($game_night->spotDifferencesGames as $game) {
                    $game['user_has_played'] = $game->userHasPlayed(auth()->user());
                }
            }

            if ($game_night->userCanPlay()) {
                $game_night['can_play'] = true;
            } else {
                $game_night['can_play'] = false;
            }
        }

        return response()->json(['message' => '', 'data' => $game_nights], 200);
    }

    public function getCreatorsChallenges()
    {
        $game_nights = GameNight::with('triviaGames.triviaQuestions', 'twoPicsGames', 'spotDifferencesGames', 'category')->where('category_id', 2)->get();

        foreach ($game_nights as $key => $game_night) {
            if ($game_night->triviaGames) {
                foreach ($game_night->triviaGames as $trivia_game) {
                    foreach ($trivia_game->triviaQuestions as $question) {
                        $question['user_has_played'] = $question->userHasPlayed(auth()->user());
                    }
                }
            }
            if ($game_night->twoPicsGames) {
                foreach ($game_night->twoPicsGames as $game) {
                    $game['user_has_played'] = $game->userHasPlayed(auth()->user());
                }
            }
            if ($game_night->spotDifferencesGames) {
                foreach ($game_night->spotDifferencesGames as $game) {
                    $game['user_has_played'] = $game->userHasPlayed(auth()->user());
                }
            }

            if ($game_night->userCanPlay()) {
                $game_night['can_play'] = true;
            } else {
                $game_night['can_play'] = false;
            }
        }

        return response()->json(['message' => '', 'data' => $game_nights], 200);
    }
}

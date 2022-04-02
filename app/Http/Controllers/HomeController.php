<?php

namespace App\Http\Controllers;

use App\School;
use App\ShopItem;
use App\SpotDifference;
use App\Trivia;
use App\TwoPicsGame;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    // Controllers for the admin dashboard
    public function getAppUsers()
    {
        $users = User::get()->count();
        return pozzy_httpOk($users - 2);
    }

    public function getGamesCount()
    {
        $total_count = 0;
        $trivia = Trivia::all()->count();
        $total_count += $trivia;
        $two_pics = TwoPicsGame::all()->count();
        $total_count += $two_pics;
        $spot_difference = SpotDifference::all()->count();
        $total_count += $spot_difference;

        return pozzy_httpOk($total_count);
    }

    public function getSchoolsCount()
    {
        $schools = School::all()->count();

        return pozzy_httpOk($schools);
    }

    public function getShopCount()
    {
        $items = ShopItem::all()->count();

        return pozzy_httpOk($items);
    }

    public function getUserRegistrationRate()
    {
        $months = [];
        $totalUsers = [];
        $days = [0, 29, 59, 89, 119, 149, 179];
        foreach ($days as $day) {
            array_push($months, Carbon::now()->subDays($day));
        }

        foreach ($months as $month) {
            $userData = User::whereBetween('created_at', [Carbon::parse($month)->startOfMonth(), Carbon::parse($month)->endOfMonth()])->count();
            array_push($totalUsers, $userData);
        }

        $monthsFormatted = [];
        foreach ($months as $month) {
            array_push($monthsFormatted, $month->format('M'));
        }

        return response()->json([
            'months' => $monthsFormatted,
            'totalUsers' => $totalUsers
        ], 200);
    }
}

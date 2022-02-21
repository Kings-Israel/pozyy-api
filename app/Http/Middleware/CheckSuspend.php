<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Auth;

class CheckSuspend
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::check() && Auth::user()->suspend && now()->greaterThan(Auth::user()->suspend)) {
            $date = Auth::user()->suspend->format('d-m-Y');
            auth()->logout();
            $message = "Your account has been suspended from ".$date.". Please contact administrator.";
            Log::info($message);
            return redirect()->route('login')->withMessage($message);
        }
        return $next($request);
    }
}

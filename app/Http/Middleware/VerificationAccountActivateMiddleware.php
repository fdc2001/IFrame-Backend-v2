<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificationAccountActivateMiddleware {
    public function handle(Request $request, Closure $next) {
        if (auth()->user()->hasVerifiedEmail()) {
            return $next($request);
        }
        return response()->json(["message" => "Email not verified.", "status"=>"Error"], 400);
    }
}

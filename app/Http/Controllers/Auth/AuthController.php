<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register', 'refresh']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials, true);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }

    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'birthday' => 'nullable|date|before:13 years ago',
            'username' => 'required|string|min:3|unique:users'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'birthday' => $request->birthday,
            'password' => Hash::make($request->password),
            'username' => Hash::make($request->username),
        ]);

        $user->sendEmailVerificationNotification();

        $token = Auth::login($user, true);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        try {
            return response()->json([
                'status' => 'success',
                'authorisation' => [
                    'token' => Auth::refresh(),
                    'type' => 'bearer',
                ]
            ]);
        }catch (\Exception $e){
            if($e instanceof TokenExpiredException){
                try {
                    $newToken = JWTAuth::parseToken()->refresh();
                    return response()->json([
                        'status' => 'success',
                        'authorisation' => [
                            'token' => $newToken,
                            'type' => 'bearer',
                        ]
                    ]);
                }catch (\Exception $e){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'The token is invalid',
                    ], 401);
                }
            }else if ($e instanceof TokenInvalidException){
                return response()->json(['status' => 'error', 'message' => 'Token is Invalid'], 401);
            }else{
                return response()->json(['status' => 'error', 'message' => 'Authorization Token not found'], 401);
            }
        }

    }

    public function verifyAccountEnable() {
        if($user = Auth::user()->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Account already verified'
            ]);
        }
        return response()->json([
            'status' => 'failed',
            'message' => 'Your account is not verified, please verify your email'
        ]);
    }
}

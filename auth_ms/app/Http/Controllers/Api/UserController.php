<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class UserController extends Controller
{
    // in this method we make user from incoming api
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|numeric|digits:11',
                'name' => 'required|string|min:3|max:50',
                'password' => 'required|min:8'
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        "data" => [
                            'status' => 'error',
                            'message' => 'validation error',
                            'errors' => $validator->errors()
                        ],
                        "serve_time" => Carbon::now()
                    ],
                    422
                );
            }
            // create user
            $user = User::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password)
            ]);
            // return response with token which validate in day
            return response()->json(
                [
                    "data" => [
                        'status' => 'success',
                        'message' => 'کاربر با موفقیت ثبت نام شد',
                        'token' => $user->createToken("auth token", [], now()->addDay())->plainTextToken
                    ],
                    "serve_time" => Carbon::now()
                ],
                200
            );
        } catch (Throwable $th) {
            return response()->json(
                [
                    "data" => [
                        'status' => 'error',
                        'message' => $th->getMessage(),
                    ],
                    "serve_time" => Carbon::now()
                ],
                500
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|numeric|digits:11',
                'password' => 'required|min:8'
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        "data" => [
                            'status' => 'error',
                            'message' => 'validation error',
                            'errors' => $validator->errors()
                        ],
                        "serve_time" => Carbon::now()
                    ],
                    422
                );
            }
            // check user exist with requested data
            if (!Auth::attempt($request->only(['phone_number', 'password']))) {
                return response()->json(
                    [
                        "data" => [
                            'status' => 'error',
                            'message' => 'شماره همراه یا رمز عبور اشتباه وارد شده است.',
                        ],
                        "serve_time" => Carbon::now()
                    ],
                    401
                );
            }

            $user = User::where('phone_number', $request->phone_number)->first();

            return response()->json(
                [
                    "data" => [
                        'status' => 'success',
                        'message' => 'کاربر با موفقیت وارد شد',
                        'token' => $user->createToken("auth token", [], now()->addDay())->plainTextToken
                    ],
                    "serve_time" => Carbon::now()
                ],
                200
            );
        } catch (Throwable $th) {
            return response()->json(
                [
                    "data" => [
                        'status' => 'error',
                        'message' => $th->getMessage(),
                    ],
                    "serve_time" => Carbon::now()
                ],
                500
            );
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        // Delete the token
        $token->delete();

        return response()->json([
            "data" => [
                'status' => 'success',
                'message' => 'کاربر با موفقیت خارج شد.'
            ],
            "serve_time" => Carbon::now()
        ], 200);
    }
}

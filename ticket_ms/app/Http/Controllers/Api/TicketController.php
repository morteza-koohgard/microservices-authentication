<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class TicketController extends Controller
{
    // I usually save token in browser cookies, i assume we receive token from front request
    public function storeTicket(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|min:3',
                'text' => 'required|string|min:10|max:1500',
                'token' => 'required|string|min:10'
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

            // for testing don't send request and set user_id = 1
            if (app()->environment() == 'testing') {
                $user_id = 1;
            } else {
                DB::setDefaultConnection('auth');
                // connect to auth microservice and find user with access token

                $user_id = DB::table('personal_access_tokens')->where('token', hash('sha256', $request->token))->first();
                if ($user_id)
                    $user_id = $user_id->tokenable_id;
                else {
                    return response()->json(
                        [
                            "data" => [
                                'status' => 'error',
                                'message' => 'کاربر از سیستم خارج شده است'
                            ],
                            "serve_time" => Carbon::now()
                        ],
                        401
                    );
                }
                DB::setDefaultConnection('mysql');
            }

            // create ticket
            Ticket::create([
                'title' => $request->title,
                'text' => $request->text,
                'user_id' => $user_id
            ]);

            return response()->json(
                [
                    "data" => [
                        'status' => 'success',
                        'message' => 'تیکت با موفقیت ثبت شد',
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
}

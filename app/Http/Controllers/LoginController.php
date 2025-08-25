<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseAPI;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class LoginController extends Controller
{
    private $responseApi;
    public function __construct()
    {
        $this->responseApi = new ResponseAPI();
    }

    public function login(Request $request)
    {
        try {
            $accessToken = $request->input('access_token');
            // Call Google API to get user info
            $response = Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
            ])->get('https://www.googleapis.com/oauth2/v3/userinfo');
            if ($response->failed()) {
                return response()->json(['error' => 'Invalid token'], 401);
            }
            $googleUser = $response->json();
            $user = User::firstOrCreate(
                ["email" => $googleUser['email']],
                [
                    "email" => $googleUser["email"],
                    "name" => $googleUser["name"],
                    "avatar" => $googleUser["picture"],
                    "password" => "",
                ]
            );
            Auth::login($user);
            $success = $user->createToken($user->id);
            $success->user_infor = $user;
            return $this->responseApi->success($success);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseApi->InternalServerError();
        }
    }
}

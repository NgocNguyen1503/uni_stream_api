<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemoController extends Controller
{
    public function list(Request $request)
    {
        // return response()->json(DB::table('users')->select('id')->pluck('id')->toArray());
        return User::all();
    }
}

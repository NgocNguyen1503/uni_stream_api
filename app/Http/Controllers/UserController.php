<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\Live;
use Illuminate\Http\Request;
use App\Helpers\ResponseAPI;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private $responseApi;

    public function __construct()
    {
        $this->responseApi = new ResponseApi();
    }

    public function index(Request $request)
    {
        $param = $request->all();
        $responseData = [];
        $responseData['my_live'] = Live::join('users', 'lives.streamer_id', 'users.id')
            ->select(
                'lives.id',
                'users.name as streamer',
                'users.avatar',
                'lives.thumbnail',
                'lives.title',
                'lives.time_start'
            )->where('lives.streamer_id', Auth::id())
            ->orderBy('lives.time_start', 'DESC')
            ->first();
        $responseData['my_live'] = Common::updateLiveInfor($responseData['my_live']);

        $responseData['list_live'] = Live::join('users', 'lives.streamer_id', 'users.id')
            ->select(
                'lives.id',
                'users.name as streamer',
                'users.avatar',
                'lives.thumbnail',
                'lives.title',
                'lives.time_start'
            )->where('lives.streamer_id', '<>', Auth::id())
            ->orderBy('lives.time_start', 'DESC')
            ->limit(config('const.live_limit'))
            ->get();
        foreach ($responseData['list_live'] as $live) {
            $live = Common::updateLiveInfor($live);
        }

        return $this->responseApi->success($responseData);
    }
}
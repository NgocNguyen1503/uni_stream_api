<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseAPI;
use App\Models\Push;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    private $responseApi;

    public function __construct()
    {
        $this->responseApi = new ResponseApi();
    }

    public function listNotification(Request $request)
    {
        $param = $request->all();
        $pushes = Push::select(
            'id',
            'title',
            'content',
            'status',
            'user_id',
            'created_at'
        )
            ->where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'DESC')
            ->skip($param['offset'])->take($param['limit'])
            ->get()->map(function ($noti) {
                $noti->time_ago = Common::timeAgo($noti->created_at);
                return $noti;
            });
        return $this->responseApi->success($pushes);
    }

    public function setDeviceToken(Request $request)
    {
        $params = $request->all();
        $user = Auth::user();
        $user->update([
            'device_token' => $params['fcmToken']
        ]);
        return $this->responseApi->success($user);
    }
}

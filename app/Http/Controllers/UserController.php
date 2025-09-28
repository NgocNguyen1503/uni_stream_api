<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\Follow;
use App\Models\Live;
use App\Models\User;
use App\Services\YoutubeLiveService;
use Illuminate\Http\Request;
use App\Helpers\ResponseAPI;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function getGoogleLiveAuthURL(Request $request)
    {
        $youtubeLiveService = new YoutubeLiveService();

        // Response redirect url to client
        return $this->responseApi->success(
            $youtubeLiveService->redirectAuthGoogle() // Client will use this url to access and get code
        );
    }

    public function saveToken(Request $request)
    {
        $param = $request->all();
        $streamerId = Auth::id();
        $youtubeLiveService = new YoutubeLiveService();
        $liveTokenData = $youtubeLiveService->renderToken($param['code'], $streamerId);

        // Save token to DB
        try {
            DB::table('users')->where('id', $streamerId)
                ->update(['google_token' => $liveTokenData['google_live_token']]);
            return $this->responseApi->success($streamerId);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseApi->InternalServerError();
        }
    }

    public function youtubeRegisterLive(Request $request)
    {
        $param = $request->all();
        $fileName = '';

        // Check thumbnail's type to upload
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $fileName = md5(date('Y-m-d H:i:s')) . '.' . $file->getClientOriginalExtension();
            // Save to public
            $file->move(public_path('/uploads/thumbnails'), $fileName);
        }

        $googleLiveToken = User::select('google_token')->where('id', Auth::id())
            ->first();
        if (is_null($googleLiveToken->google_token)) {
            return $this->responseApi->UnAuthorization();
        }

        // Create new live session
        $youtubeLiveService = new YoutubeLiveService();
        $liveSession = $youtubeLiveService->createLiveStream(
            $param['title'],
            $googleLiveToken->google_token
        );
        $live = new Live();
        $live->streamer_id = Auth::id();
        $live->title = $param['title'];
        $live->thumbnail = $fileName;
        $live->time_start = $param['start_time'];
        $live->time_end = Carbon::create($param['start_time'])->addHours(2);
        $live->stream_url = $liveSession['stream_url'];
        $live->stream_key = $liveSession['stream_key'];
        $live->watch_url = $liveSession['watch_url'];
        $live->embed_url = $liveSession['embed_url'];
        $live->save();

        return $this->responseApi->success($live);
    }

    public function liveDetail(Request $request)
    {
        $param = $request->all();
        $currentLive = Live::join('users', 'lives.streamer_id', 'users.id')
            ->with('streamer', 'streamer.follows')
            ->select(
                'lives.id',
                'users.name as streamer_name',
                'users.avatar',
                'lives.thumbnail',
                'lives.title',
                'lives.time_start',
                'lives.watch_url',
                'lives.embed_url',
                'lives.stream_url',
                'lives.stream_key',
                'lives.streamer_id'
            )->where('lives.id', $param['live_id'])
            ->first();

        $isFollowed = Follow::NOT_FOLLOWED;
        if (!is_null($currentLive->streamer->follows)) {
            foreach ($currentLive->streamer->follows as $follow) {
                if ($follow->user_id == Auth::id()) {
                    $isFollowed == Follow::FOLLOWED;
                }
            }
        }

        // Check if user is watching his own streaming
        if ($currentLive->streamer_id == Auth::id()) {
            $isFollowed = Follow::OWNER;
        }

        $currentLive->follows = $isFollowed;
        unset($currentLive->streamer);

        $listLive = Live::join('users', 'lives.streamer_id', 'users.id')
            ->select(
                'lives.id',
                'users.name as streamer_name',
                'users.avatar',
                'lives.thumbnail',
                'lives.title',
                'lives.time_start',
            )->where('lives.id', '<>', $param['live_id'])
            ->limit(config('const.recommendation_live_limit'))->get()
            ->map(function ($item) {
                return Common::updateLiveInfor($item);
            });

        return $this->responseApi->success([
            'live' => $currentLive,
            'list_live' => $listLive
        ]);
    }
}
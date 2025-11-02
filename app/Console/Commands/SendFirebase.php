<?php

namespace App\Console\Commands;

use App\Models\Push;
use App\Services\FirebaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendFirebase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-firebase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notifications = Push::join('users', 'pushes.user_id', 'users.id')
            ->select('pushes.id', 'pushes.title', 'pushes.content', 'users.device_token')
            ->where('status', Push::STATUS_WAIT)
            ->get();
        if (!$notifications) {
            return null;
        }

        $firebaseService = new FirebaseService();
        foreach ($notifications as $notification) {
            $sendFCM = $firebaseService->sendFCM(
                $notification->content,
                $notification->device_token
            );

            if ($sendFCM) {
                DB::table('pushes')->where('id', $notification->id)
                    ->update([
                        'status' => Push::STATUS_DONE
                    ]);
                continue;
            }
            DB::table('pushes')->where('id', $notification->id)
                ->update([
                    'status' => Push::STATUS_FAIL
                ]);
        }
    }
}

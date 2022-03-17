<?php

namespace App\Providers;

use App\Providers\LoginHistory;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class StoreUserLoginHistory
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param LoginHistory $event
     * @return void
     */
    public function handle(LoginHistory $event)
    {
        $current_timestamp = Carbon::now()->toDateTimeString();

        $userinfo = $event->user;
        $userinfo->last_login_ip = \Request::ip();
        $userinfo->last_login = $current_timestamp;
        $userinfo->login_counter = $userinfo->login_counter + 1;
        $userinfo->save();

        return $userinfo;
    }
}

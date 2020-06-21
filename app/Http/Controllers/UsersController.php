<?php

namespace App\Http\Controllers;

use App\User;
use App\Events\UserOnlines;
use App\Events\UserOffline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
         * Get 1 users (currently only can specific 1 user due to this chat application only 1-to-1)
         *
         * @param  Request $request
         * @return Response
         */
    public function getUser(Request $request)
    {
        $user = User::find(2);
        
        return ['online_status'=>$user->online_status];
    }

    /**
         * Update user status to offline
         *
         * @param  Request $request
         * @return Response
         */
    public function offline(Request $request)
    {
        $user = User::find($request->input('id'));
    
        $user->online_status = 'offline';
    
        $user->save();
    
        broadcast(new UserOffline($user));
    }
}

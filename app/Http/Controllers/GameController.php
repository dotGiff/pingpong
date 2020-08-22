<?php

namespace App\Http\Controllers;

use App\Jobs\SendSlackMessage;
use App\User;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function looking(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string'
        ]);

        $user = User::firstOrCreate(['username' => $request->get('username')]);

        dispatch(new SendSlackMessage('pongbot', $user->looking()));

        return response('', 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function join(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string'
        ]);

        $user = User::firstOrCreate(['username' => $request->get('username')]);

        dispatch(new SendSlackMessage('pongbot', $user->join()));

        return response('', 200);
    }
}

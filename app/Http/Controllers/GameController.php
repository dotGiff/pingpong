<?php

namespace App\Http\Controllers;

use App\Jobs\SendSlackMessage;
use App\User;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    /**
     * @param Request $request
     * @return ResponseFactory|Response
     * @throws ValidationException
     */
    public function looking(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string'
        ]);

        $user = User::firstOrCreate(['username' => $request->get('username')]);

        dispatch(new SendSlackMessage('pongbot', $user->looking()->getMessage()));

        return response('', 200);
    }

    /**
     * @param Request $request
     * @return ResponseFactory|Response
     * @throws ValidationException
     */
    public function join(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string'
        ]);

        $user = User::firstOrCreate(['username' => $request->get('username')]);

        dispatch(new SendSlackMessage('pongbot', $user->join()->getMessage()));

        return response('', 200);
    }
}

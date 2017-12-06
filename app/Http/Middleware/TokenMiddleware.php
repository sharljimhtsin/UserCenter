<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:50:55
 */

namespace App\Http\Middleware;

use App\Token;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Http\Response;

class TokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->exists("token")) {
            return \response()->json(["error" => "token null"]);
        }
        if (!$request->exists("user_id")) {
            return \response()->json(["error" => "user_id null"]);
        }
        $token = $request->input("token");
        $user_id = $request->input("user_id");
        $tokenResult = Token::query()->find($user_id);
        if ($tokenResult) {
            $tokenObj = $tokenResult->toArray();
            if ($token != $tokenObj["token"]) {
                return \response()->json(["error" => "token error"]);
            }
            if (time() > $tokenObj["expire_time"]) {
                return \response()->json(["error" => "token expired"]);
            }
        } else {
            return \response()->json(["error" => "token error"]);
        }
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        var_dump("terminate");
    }
}
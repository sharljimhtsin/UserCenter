<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:50:55
 */

namespace App\Http\Middleware;

use App\Lib\Utils;
use App\Token;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Http\Response;

class TokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->exists("token")) {
            return Utils::echoContent(Utils::CODE_TOKEN_NULL);
        }
        if (!$request->exists("user_id")) {
            return Utils::echoContent(Utils::CODE_USER_ID_NULL);
        }
        $token = $request->input("token");
        $user_id = $request->input("user_id");
        $tokenResult = Token::query()->find($user_id);
        if ($tokenResult) {
            $tokenObj = $tokenResult->toArray();
            if ($token != $tokenObj["token"]) {
                return Utils::echoContent(Utils::CODE_TOKEN_ERROR);
            }
            if (time() > $tokenObj["expire_time"]) {
                return Utils::echoContent(Utils::CODE_TOKEN_EXPIRED);
            }
        } else {
            return Utils::echoContent(Utils::CODE_TOKEN_ERROR);
        }
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        var_dump("terminate");
    }
}
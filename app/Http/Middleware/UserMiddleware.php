<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 11:10:24
 */

namespace App\Http\Middleware;


use Illuminate\Http\Request;
use Closure;
use Illuminate\Http\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->exists("user_id")) {
            return \response()->json(["error" => "user_id null"]);
        }
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        var_dump("terminate");
    }
}
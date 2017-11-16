<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:50:55
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Http\Response;

class TokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->exists("token")) {
            return "token error";
        }
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        var_dump("terminate");
    }
}
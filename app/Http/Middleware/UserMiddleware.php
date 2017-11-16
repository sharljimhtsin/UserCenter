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
        $excludeApi = ["login"];
        $urls = explode("/", $request->getRequestUri());
        if (!$request->exists("token") && !in_array($urls[2], $excludeApi)) {
            return "error";
        }
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        var_dump("terminate");
    }
}
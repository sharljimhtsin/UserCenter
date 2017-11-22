<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class AdminAuthenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $userObj = User::getQuery()->find($request->input("user_id"))->toArray();
        if ($userObj["role"] != User::ADMIN_ROLE) {
            return response()->json(["error" => "Unauthorized"])->setStatusCode(401);
        }
        return $next($request);
    }
}

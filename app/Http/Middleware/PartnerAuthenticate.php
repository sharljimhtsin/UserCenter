<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/22
 * Time: 10:58:35
 */

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;

class PartnerAuthenticate
{
    protected $auth;

    /**
     * PartnerAuthenticate constructor.
     * @param $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return mixed
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param mixed $auth
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param $request Request
     * @param Closure $next
     * @param null $guard
     * @return $this|mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $userObj = User::getQuery()->find($request->input("user_id"))->toArray();
        if ($userObj["role"] != User::PARTNER_ROLE) {
            return response()->json(["error" => "Unauthorized"])->setStatusCode(401);
        }
        return $next($request);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 10:53:02
 */

namespace App\Http\Controllers;


use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->middleware("user", ['except' => ['login']]);
    }

    public function index(Request $request)
    {
        return "user";
    }

    public function login(Request $request)
    {
        $userList = User::all();
        var_dump($userList);

        return response()->json(["aaa" => "bb"]);
    }

    public function info(Request $request)
    {
        $flight = User::query()->where("username", "=", "rinimabi")->first();
        $flight->ip = "2222222";
        $flight->save();
//        var_dump($flight);
        return "";
    }
}
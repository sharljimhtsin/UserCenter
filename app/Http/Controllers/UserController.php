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
        $this->middleware("token", ['except' => ['index']]);
    }

    public function index(Request $request)
    {
        return "user";
    }

    public function updateInfo(Request $request)
    {
        $user_id = $request->input("user_id");
        $nickname = $request->input("nickname");
        $avatar = $request->input("avatar");
        $birthday = $request->input("birthday");
        $sex = $request->input("sex");
        $signature = $request->input("signature");
        $result = User::getQuery()->find($user_id);
        if (is_null($result)) {
            return response()->json(["error" => "user not exist"]);
        } else {
            $obj = $result->toArray();
            $obj["nickname"] = $nickname;
            $obj["avatar"] = $avatar;
            $obj["birthday"] = $birthday;
            $obj["sex"] = $sex;
            $obj["signature"] = $signature;
            $result->fill($obj)->save();
            return response()->json(["user" => $obj]);
        }
    }

    public function info(Request $request)
    {
        $user_id = $request->input("user_id");
        $result = User::getQuery()->find($user_id);
        if (is_null($result)) {
            return response()->json(["error" => "user not exist"]);
        } else {
            return response()->json(["user" => $result->toArray()]);
        }
    }
}
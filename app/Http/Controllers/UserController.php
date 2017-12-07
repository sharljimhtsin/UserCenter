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
        $this->middleware("token", ['except' => []]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        /**
         * @var $userResult User
         */
        $userResult = $request->user();
        if (is_null($userResult)) {
            return response()->json(["error" => "user not exist"]);
        } else {
            $userObj = $userResult->toArray();
            return response()->json(["user" => $userObj]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateInfo(Request $request)
    {
        $user_id = $request->input("user_id");
        $nickname = $request->input("nickname", null);
        $avatar = $request->input("avatar", null);
        $birthday = $request->input("birthday", null);
        $sex = $request->input("sex", null);
        $signature = $request->input("signature", null);
        $result = User::getQuery()->find($user_id);
        if (is_null($result)) {
            return response()->json(["error" => "user not exist"]);
        } else {
            $obj = $result->toArray();
            $obj["nickname"] = $nickname ? $nickname : $obj["nickname"];
            $obj["avatar"] = $avatar ? $avatar : $obj["avatar"];
            $obj["birthday"] = $birthday ? $birthday : $obj["birthday"];
            $obj["sex"] = $sex ? intval($sex) : $obj["sex"];
            $obj["signature"] = $signature ? $signature : $obj["signature"];
            $result->fill($obj)->save();
            return response()->json(["user" => $obj]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
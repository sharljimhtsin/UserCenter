<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 10:53:02
 */

namespace App\Http\Controllers;


use App\Lib\Utils;
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
            return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
        } else {
            $userObj = $userResult->toArray();
            return Utils::echoContent(Utils::CODE_OK, ["user" => $userObj]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateInfo(Request $request)
    {
        $this->validate($request, ["birthday" => "nullable|date"]);
        $user_id = $request->input("user_id");
        $nickname = $request->input("nickname", null);
        $avatar = $request->input("avatar", null);
        $birthday = $request->input("birthday", null);
        $sex = $request->input("sex", null);
        $signature = $request->input("signature", null);
        $result = User::getQuery()->find($user_id);
        if (is_null($result)) {
            return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
        } else {
            $obj = $result->toArray();
            $obj["nickname"] = $nickname ? $nickname : $obj["nickname"];
            $obj["avatar"] = $avatar ? $avatar : $obj["avatar"];
            $obj["birthday"] = $birthday ? $birthday : $obj["birthday"];
            $obj["sex"] = $sex ? intval($sex) : $obj["sex"];
            $obj["signature"] = $signature ? $signature : $obj["signature"];
            $result->fill($obj)->save();
            return Utils::echoContent(Utils::CODE_OK, ["user" => $obj]);
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
            return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
        } else {
            return Utils::echoContent(Utils::CODE_OK, ["user" => $result->toArray()]);
        }
    }
}
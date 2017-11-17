<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:46:59
 */

namespace App\Http\Controllers;


use App\Account;
use App\Token;
use App\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{

    /**
     * AccountController constructor.
     */
    public function __construct()
    {
        $this->middleware('token', ['only' => ["index"]]);
    }

    public function index(Request $request)
    {
        return "OK";
    }

    private function getRandomToken($size = 10)
    {
        return \bin2hex(\random_bytes($size));
    }

    private function getUniqueUid($prefix)
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8);
        $uuid .= substr($chars, 8, 4);
        $uuid .= substr($chars, 12, 4);
        $uuid .= substr($chars, 16, 4);
        $uuid .= substr($chars, 20, 12);
        return $prefix . $uuid;
    }

    public function login(Request $request)
    {
        $user_key = $request->input("user_key", "qwerty");
        $password = $request->input("password", "123456");
        $result = Account::query()->where([['user_key', '=', $user_key], ['password', '=', md5($password)]])->first();
        if ($result) {
            $accountObj = $result->toArray();
            $userResult = User::query()->find($accountObj["union_user_id"]);
            if ($userResult) {
                $userObj = $userResult->toArray();
                $tokenStr = $this->getRandomToken();
                Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "ttl" => $this->getTokenTTLTime()]);
                return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
            } else {
                return response()->json(["error" => "user not exist"]);
            }
        } else {
            return response()->json(["error" => "account not exist"]);
        }
    }

    public function tempLogin(Request $request)
    {
        $user_key = $request->input("device_id", "qwerty");
        $result = Account::query()->firstOrNew(['user_key' => $user_key], ["account_type" => Account::TEMP_LOGIN]);
        $accountObj = $result->toArray();
        if (!array_key_exists("union_user_id", $accountObj)) {
            $accountObj["union_user_id"] = $this->genUserUid();
            $result->fill($accountObj);
            $result->save();
        }
        $userResult = User::query()->findOrNew($accountObj["union_user_id"]);
        $userObj = $userResult->toArray();
        if (empty($userObj)) {
            $userObj["user_id"] = $accountObj["union_user_id"];
            $userResult->fill($userObj);
            $userResult->save();
        }
        $tokenStr = $this->getRandomToken();
        Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "ttl" => $this->getTokenTTLTime()]);
        return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
    }

    private function genUserUid()
    {
        return rand(1000, 9999);
    }

    private function getTokenTTLTime()
    {
        return time() + 60 * 60 * 1;
    }
}
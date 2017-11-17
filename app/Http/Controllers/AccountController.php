<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:46:59
 */

namespace App\Http\Controllers;


use App\Account;
use App\SmsCode;
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
        $this->middleware('token', ['only' => ["index", "sendSmsCode", "bindPhone"]]);
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

    public function sendSmsCode(Request $request)
    {
        $telephone = $request->input("telephone", "13800138000");
        $user_id = $request->input("user_id", "9138");
        $userResult = User::query()->find($user_id);
        if ($userResult) {
            $userObj = $userResult->toArray();
            $telephoneExist = User::query()->where([["telephone", "=", $telephone], ["user_id", "!=", $user_id]])->count("user_id");
            if ((is_null($userObj["telephone"]) || $telephone == $userObj["telephone"]) && $telephoneExist == 0) {
                $smsCodeStr = $this->genRandomSmsCode();
                SmsCode::query()->updateOrCreate(["telephone" => $telephone], ["telephone" => $telephone, "code" => $smsCodeStr, "ttl" => $this->getSmsCodeTTLTime()]);
                return response()->json(["smsCode" => $smsCodeStr]);
            } else {
                return response()->json(["error" => "telephone not match"]);
            }
        } else {
            return response()->json(["error" => "user not exist"]);
        }
    }

    public function bindPhone(Request $request)
    {
        $telephone = $request->input("telephone", "13800138000");
        $smsCode = $request->input("smsCode", "0000");
        $user_id = $request->input("user_id", "9138");
        $userResult = User::query()->find($user_id);
        if ($userResult) {
            $userObj = $userResult->toArray();
            $telephoneExist = User::query()->where([["telephone", "=", $telephone], ["user_id", "!=", $user_id]])->count("user_id");
            if (is_null($userObj["telephone"]) && $telephoneExist == 0) {
                $smsCodeResult = SmsCode::query()->find($telephone);
                if (is_null($smsCodeResult)) {
                    return response()->json(["error" => "smsCode error"]);
                }
                $smsCodeObject = $smsCodeResult->toArray();
                if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["ttl"]) {
                    return response()->json(["error" => "smsCode invalid"]);
                }
                $userObj["telephone"] = $telephone;
                $userResult->fill($userObj)->save();
                $accountResult = Account::query()->create(["user_key" => $telephone, "account_type" => Account::TELEPHONE_LOGIN, "union_user_id" => $user_id, "status" => Account::NORMAL_STATUS]);
                return response()->json(["account" => $accountResult->toArray()]);
            } else {
                return response()->json(["error" => "telephone error"]);
            }
        } else {
            return response()->json(["error" => "user not exist"]);
        }
    }

    private function genUserUid()
    {
        return rand(1000, 9999);
    }

    private function genRandomSmsCode()
    {
        return rand(1000, 9999);
    }

    private function getTokenTTLTime()
    {
        return time() + 60 * 60 * 1;
    }

    private function getSmsCodeTTLTime()
    {
        return time() + 60 * 1;
    }
}
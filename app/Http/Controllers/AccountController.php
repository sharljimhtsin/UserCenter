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
        $this->middleware("token", ["only" => ["index", "sendSmsCode", "bindPhone", "modifyPassword"]]);
    }

    /**
     * @param Request $request
     * @return string
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $user_key = $request->input("user_key", "qwerty");
        $password = $request->input("password", "123456");
        $result = Account::query()->where([["user_key", "=", $user_key], ["password", "=", md5($password)], ["account_type", "=", Account::NORMAL_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function telephoneLogin(Request $request)
    {
        $telephone = $request->input("telephone", "13800138000");
        $smsCode = $request->input("smsCode", "0000");
        $smsCodeResult = SmsCode::query()->find($telephone);
        if (is_null($smsCodeResult)) {
            return response()->json(["error" => "smsCode error"]);
        }
        $smsCodeObject = $smsCodeResult->toArray();
        if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["ttl"]) {
            return response()->json(["error" => "smsCode invalid"]);
        }
        $accountResult = Account::query()->where([["user_key", "=", $telephone], ["account_type", "=", Account::TELEPHONE_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if (is_null($accountResult)) {
            return response()->json(["error" => "account not exist"]);
        }
        $accountObj = $accountResult->toArray();
        if (is_null($accountObj["union_user_id"])) {
            return response()->json(["error" => "account error"]);
        }
        $userResult = User::query()->find($accountObj["union_user_id"]);
        if ($userResult) {
            $userObj = $userResult->toArray();
            $tokenStr = $this->getRandomToken();
            Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "ttl" => $this->getTokenTTLTime()]);
            return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
        } else {
            return response()->json(["error" => "user not exist"]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tempLogin(Request $request)
    {
        $user_key = $request->input("device_id", "qwerty");
        $result = Account::query()->firstOrNew(["user_key" => $user_key, "account_type" => Account::TEMP_LOGIN, "status" => Account::NORMAL_STATUS], []);
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
            $userObj["role"] = User::NORMAL_ROLE;
            $userObj["status"] = User::NORMAL_STATUS;
            $userResult->fill($userObj);
            $userResult->save();
        }
        $tokenStr = $this->getRandomToken();
        Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "ttl" => $this->getTokenTTLTime()]);
        return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendSmsCodeNoToken(Request $request)
    {
        $telephone = $request->input("telephone", "13800138000");
        $smsCodeStr = $this->genRandomSmsCode();
        SmsCode::query()->updateOrCreate(["telephone" => $telephone], ["telephone" => $telephone, "code" => $smsCodeStr, "ttl" => $this->getSmsCodeTTLTime()]);
        return response()->json(["smsCode" => $smsCodeStr]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                // once telephone bind-ed,disable quick login of it
                Account::query()->where([["union_user_id", "=", $user_id], ["account_type", "=", Account::TEMP_LOGIN]])->update(["status" => Account::DISABLE_STATUS]);
                return response()->json(["account" => $accountResult->toArray()]);
            } else {
                return response()->json(["error" => "telephone error"]);
            }
        } else {
            return response()->json(["error" => "user not exist"]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $user_key = $request->input("user_key", "qwerty");
        $password = $request->input("password", "123456");
        $result = Account::getQuery()->where([["user_key", "=", $user_key], ["account_type", "=", Account::NORMAL_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if ($result) {
            return response()->json(["error" => "account exist"]);
        } else {
            $user_id = $this->genUserUid();
            $accountResult = Account::query()->create(["user_key" => $user_key, "password" => md5($password), "account_type" => Account::NORMAL_LOGIN, "union_user_id" => $user_id, "status" => Account::NORMAL_STATUS]);
            $accountObj = $accountResult->toArray();
            $userResult = User::query()->create(["user_id" => $user_id, "status" => User::NORMAL_STATUS, "role" => User::NORMAL_ROLE]);
            $userObj = $userResult->toArray();
            $tokenStr = $this->getRandomToken();
            Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "ttl" => $this->getTokenTTLTime()]);
            return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function modifyPassword(Request $request)
    {
        $user_id = $request->input("user_id", "0000");
        $oldPassword = $request->input("oldPassword", "4321");
        $newPassword = $request->input("newPassword", "1234");
        $accountResult = Account::query()->where([["union_user_id", "=", $user_id], ["account_type", "=", Account::NORMAL_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if (is_null($accountResult)) {
            return response()->json(["error" => "account not exist"]);
        }
        $accountObj = $accountResult->toArray();
        if (is_null($accountObj["password"]) || md5($oldPassword) == $accountObj["password"]) {
            $accountObj["password"] = md5($newPassword);
            $accountResult->fill($accountObj)->save();
            return response()->json(["account" => $accountObj]);
        } else {
            return response()->json(["error" => "password not match"]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $telephone = $request->input("telephone", "13800138000");
        $smsCode = $request->input("smsCode", "0000");
        $newPassword = $request->input("newPassword", "1234");
        $smsCodeResult = SmsCode::query()->find($telephone);
        if (is_null($smsCodeResult)) {
            return response()->json(["error" => "smsCode error"]);
        }
        $smsCodeObject = $smsCodeResult->toArray();
        if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["ttl"]) {
            return response()->json(["error" => "smsCode invalid"]);
        }
        $accountResult = Account::query()->where([["user_key", "=", $telephone], ["account_type", "=", Account::TELEPHONE_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if (is_null($accountResult)) {
            return response()->json(["error" => "account not exist"]);
        }
        $accountObj = $accountResult->toArray();
        if (is_null($accountObj["union_user_id"])) {
            return response()->json(["error" => "account error"]);
        }
        $modifyResult = Account::query()->where([["union_user_id", "=", $accountObj["union_user_id"]], ["account_type", "=", Account::NORMAL_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if ($modifyResult) {
            $modifyObj = $modifyResult->toArray();
            $modifyObj["password"] = md5($newPassword);
            $modifyResult->fill($modifyObj)->save();
            return response()->json(["account" => $modifyObj]);
        } else {
            return response()->json(["error" => "account not exist"]);
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
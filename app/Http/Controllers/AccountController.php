<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/16
 * Time: 17:46:59
 */

namespace App\Http\Controllers;


use App\Account;
use App\Channel;
use App\Mapping;
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
        $this->middleware("token", ["only" => ["index", "sendSmsCode", "verifyPhone", "modifyPassword"]]);
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 常规登录接口
     * 用户标志 user_key
     * 密码 password
     *
     */
    public function login(Request $request)
    {
        $this->validate($request, ["user_key" => "required", "password" => "required|alpha_num|alpha_dash|min:6"]);
        $user_key = $request->input("user_key", "qwerty");
        $password = $request->input("password", "123456");
        $result = Account::query()->where([["user_key", "=", $user_key], ["password", "=", md5($password)], ["account_type", "=", Account::NORMAL_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if ($result) {
            $accountObj = $result->toArray();
            $userResult = User::query()->find($accountObj["union_user_id"]);
            if ($userResult) {
                $userObj = $userResult->toArray();
                $tokenStr = $this->getRandomToken();
                Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
                return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
            } else {
                return response()->json(["error" => "user not exist"]);
            }
        } else {
            return response()->json(["error" => "account not exist or password error"]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 手机登录
     * 手机号 telephone
     * 密码 password
     */
    public function telephoneLogin(Request $request)
    {
        $this->validate($request, ["telephone" => ["required", "regex:/^((\d3)|(\d{3}\-))?13[0-9]\d{8}|15[89]\d{8}|18[0-9]\d{8}/"], "password" => "required|alpha_num|alpha_dash|min:6"]);
        $telephone = $request->input("telephone", "qwerty");
        $password = $request->input("password", "123456");
        $result = Account::query()->where([["user_key", "=", $telephone], ["password", "=", md5($password)], ["account_type", "=", Account::TELEPHONE_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if ($result) {
            $accountObj = $result->toArray();
            $userResult = User::query()->find($accountObj["union_user_id"]);
            if ($userResult) {
                $userObj = $userResult->toArray();
                $tokenStr = $this->getRandomToken();
                Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
                return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
            } else {
                return response()->json(["error" => "user not exist"]);
            }
        } else {
            return response()->json(["error" => "account not exist or password error"]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 手机快速登录
     * 手机号 telephone
     * 验证码 smsCode
     */
    public function telephoneQuickLogin(Request $request)
    {
        $this->validate($request, ["telephone" => ["required", "regex:/^((\d3)|(\d{3}\-))?13[0-9]\d{8}|15[89]\d{8}|18[0-9]\d{8}/"], "smsCode" => "required"]);
        $telephone = $request->input("telephone", "13800138000");
        $smsCode = $request->input("smsCode", "0000");
        $smsCodeResult = SmsCode::query()->find($telephone);
        if (is_null($smsCodeResult)) {
            return response()->json(["error" => "smsCode error"]);
        }
        $smsCodeObject = $smsCodeResult->toArray();
        if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["expire_time"]) {
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
            Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
            return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
        } else {
            return response()->json(["error" => "user not exist"]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 临时登录
     * 设备号 device_id
     */
    public function tempLogin(Request $request)
    {
        $this->validate($request, ["device_id" => "required"]);
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
            $userObj["nickname"] = "游客_" . $this->getRandomSuffix();
            $userObj["avatar"] = "http://api.playsm.com/resource/img/avator.png";
            $userObj["birthday"] = date("Y-m-d H:i:s", strtotime("2000-01-01"));
            $userObj["sex"] = User::SEX_MALE;
            $userObj["signature"] = "这家伙很萌,什么也没留下";
            $userObj["role"] = User::NORMAL_ROLE;
            $userObj["status"] = User::NORMAL_STATUS;
            $userResult->fill($userObj);
            $userResult->save();
        }
        $tokenStr = $this->getRandomToken();
        Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
        return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 发送验证码（带对应user_id 验证token）
     * telephone 手机号
     * user_id 用户ID
     *
     */
    public function sendSmsCode(Request $request)
    {
        $this->validate($request, ["telephone" => ["required", "regex:/^((\d3)|(\d{3}\-))?13[0-9]\d{8}|15[89]\d{8}|18[0-9]\d{8}/"], "user_id" => "required"]);
        $last_time = isset($_SESSION["SmsCd"]) ? $_SESSION["SmsCd"] : time();
        if ($last_time > time()) {
            return response()->json(["error" => "send smsCode per 1 min"]);
        } else {
            $_SESSION["SmsCd"] = time() + 60;// 1 min CDing
        }
        $telephone = $request->input("telephone", "13800138000");
        $user_id = $request->input("user_id", "9138");
        $userResult = User::query()->find($user_id);
        if ($userResult) {
            $userObj = $userResult->toArray();
            $telephoneExist = User::query()->where([["telephone", "=", $telephone], ["user_id", "!=", $user_id]])->count("user_id");
            if ((is_null($userObj["telephone"]) || $telephone == $userObj["telephone"]) && $telephoneExist == 0) {
                $smsCodeStr = $this->genRandomSmsCode();
                SmsCode::query()->updateOrCreate(["telephone" => $telephone], ["telephone" => $telephone, "code" => $smsCodeStr, "expire_time" => $this->getSmsCodeTTLTime()]);
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
     *
     * 发送验证码（不带对应user_id 不验证token）
     * telephone 手机号
     *
     */
    public function sendSmsCodeNoToken(Request $request)
    {
        $this->validate($request, ["telephone" => ["required", "regex:/^((\d3)|(\d{3}\-))?13[0-9]\d{8}|15[89]\d{8}|18[0-9]\d{8}/"]]);
        $last_time = isset($_SESSION["SmsCd"]) ? $_SESSION["SmsCd"] : time();
        if ($last_time > time()) {
            return response()->json(["error" => "send smsCode per 1 min"]);
        } else {
            $_SESSION["SmsCd"] = time() + 60;// 1 min CDing
        }
        $telephone = $request->input("telephone", "13800138000");
        $smsCodeStr = $this->genRandomSmsCode();
        SmsCode::query()->updateOrCreate(["telephone" => $telephone], ["telephone" => $telephone, "code" => $smsCodeStr, "expire_time" => $this->getSmsCodeTTLTime()]);
        return response()->json(["smsCode" => $smsCodeStr]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 认证手机号（换绑专用）
     * telephone 手机号
     * smsCode 验证码
     * user_id 用户ID
     */
    public function verifyPhone(Request $request)
    {
        $this->validate($request, ["telephone" => ["required", "regex:/^((\d3)|(\d{3}\-))?13[0-9]\d{8}|15[89]\d{8}|18[0-9]\d{8}/"], "smsCode" => "required", "user_id" => "required"]);
        $telephone = $request->input("telephone", "13800138000");
        $smsCode = $request->input("smsCode", "0000");
        $user_id = $request->input("user_id", "9138");
        $userResult = User::query()->find($user_id);
        if ($userResult) {
            $userObj = $userResult->toArray();
            if ($telephone == $userObj["telephone"]) {
                $smsCodeResult = SmsCode::query()->find($telephone);
                if (is_null($smsCodeResult)) {
                    return response()->json(["error" => "smsCode error"]);
                }
                $smsCodeObject = $smsCodeResult->toArray();
                if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["expire_time"]) {
                    return response()->json(["error" => "smsCode invalid"]);
                }
                // 存入 session tag 以便下一步操作
                $_SESSION["reBind"] = "1";
                return response()->json(["msg" => "OK", "code" => "0"]);
            } else {
                return response()->json(["error" => "telephone error"]);
            }
        } else {
            return response()->json(["error" => "user not exist"]);
        }
    }

    private function getRandomSuffix($size = 3)
    {
        return bin2hex(random_bytes($size));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 绑定手机号
     * telephone 手机号
     * smsCode 验证码
     * user_id 用户ID
     */
    public function bindPhone(Request $request)
    {
        $telephone = $request->input("telephone", "13800138000");
        $smsCode = $request->input("smsCode", "0000");
        $password = $request->input("password", "0000");
        $user_id = $request->input("user_id", "0000");
        $token = $request->input("token", "0000");
        //注册
        if (!$request->has("user_id")) {
            $this->validate($request, ["telephone" => ["required", "regex:/^((\d3)|(\d{3}\-))?13[0-9]\d{8}|15[89]\d{8}|18[0-9]\d{8}/"], "smsCode" => "required", "password" => "required|alpha_num|alpha_dash|min:6"]);
            $telephoneExist = User::query()->where([["telephone", "=", $telephone]])->count("user_id");
            if ($telephoneExist > 0) {
                return response()->json(["error" => "telephone exist"]);
            }
            $smsCodeResult = SmsCode::query()->find($telephone);
            if (is_null($smsCodeResult)) {
                return response()->json(["error" => "smsCode error"]);
            }
            $smsCodeObject = $smsCodeResult->toArray();
            if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["expire_time"]) {
                return response()->json(["error" => "smsCode invalid"]);
            }
            $result = Account::getQuery()->where([["user_key", "=", $telephone], ["account_type", "=", Account::TELEPHONE_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
            if ($result) {
                return response()->json(["error" => "account exist"]);
            } else {
                $user_id = $this->genUserUid();
                $accountResult = Account::query()->create(["user_key" => $telephone, "password" => md5($password), "account_type" => Account::TELEPHONE_LOGIN, "union_user_id" => $user_id, "status" => Account::NORMAL_STATUS]);
                $accountObj = $accountResult->toArray();
                $userResult = User::query()->create(["user_id" => $user_id]);
                $userObj = $userResult->toArray();
                $userObj["nickname"] = "用户_" . $this->getRandomSuffix();
                $userObj["telephone"] = $telephone;
                $userObj["avatar"] = "http://api.playsm.com/resource/img/avator.png";
                $userObj["birthday"] = date("Y-m-d H:i:s", strtotime("2000-01-01"));
                $userObj["sex"] = User::SEX_MALE;
                $userObj["signature"] = "这家伙很萌,什么也没留下";
                $userObj["role"] = User::NORMAL_ROLE;
                $userObj["status"] = User::NORMAL_STATUS;
                $userResult->fill($userObj);
                $userResult->save();
                $tokenStr = $this->getRandomToken();
                Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
                return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
            }
        } else {
            //绑定、换绑手机号
            if (isset($_SESSION["reBind"])) {
                $reBind = true;
                $this->validate($request, ["telephone" => ["required", "regex:/^((\d3)|(\d{3}\-))?13[0-9]\d{8}|15[89]\d{8}|18[0-9]\d{8}/"], "smsCode" => "required", "user_id" => "required", "token" => "required"]);
            } else {
                $reBind = false;
                $this->validate($request, ["telephone" => ["required", "regex:/^((\d3)|(\d{3}\-))?13[0-9]\d{8}|15[89]\d{8}|18[0-9]\d{8}/"], "smsCode" => "required", "password" => "required|alpha_num|alpha_dash|min:6", "user_id" => "required", "token" => "required"]);
            }
            $userResult = User::query()->find($user_id);
            if ($userResult) {
                $tokenResult = Token::query()->find($user_id);
                if ($tokenResult) {
                    $tokenObj = $tokenResult->toArray();
                    if ($token != $tokenObj["token"]) {
                        return response()->json(["error" => "token error"]);
                    }
                    if (time() > $tokenObj["expire_time"]) {
                        return response()->json(["error" => "token expired"]);
                    }
                } else {
                    return response()->json(["error" => "token error"]);
                }
                $userObj = $userResult->toArray();
                $notBind = is_null($userObj["telephone"]);
                $telephoneExist = User::query()->where([["telephone", "=", $telephone], ["user_id", "!=", $user_id]])->count("user_id");
                if (($notBind || (!$notBind && $reBind)) && $telephoneExist == 0) {
                    $smsCodeResult = SmsCode::query()->find($telephone);
                    if (is_null($smsCodeResult)) {
                        return response()->json(["error" => "smsCode error"]);
                    }
                    $smsCodeObject = $smsCodeResult->toArray();
                    if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["expire_time"]) {
                        return response()->json(["error" => "smsCode invalid"]);
                    }
                    $userObj["telephone"] = $telephone;
                    $userResult->fill($userObj)->save();
                    $accountResult = Account::query()->create(["user_key" => $telephone, "password" => md5($password), "account_type" => Account::TELEPHONE_LOGIN, "union_user_id" => $user_id, "status" => Account::NORMAL_STATUS]);
                    // once telephone bind-ed,disable quick login of it
                    Account::query()->where([["union_user_id", "=", $user_id], ["account_type", "=", Account::TEMP_LOGIN]])->update(["status" => Account::DISABLE_STATUS]);
                    unset($_SESSION["reBind"]);
                    return response()->json(["account" => $accountResult->toArray()]);
                } else {
                    return response()->json(["error" => "telephone error"]);
                }
            } else {
                return response()->json(["error" => "user not exist"]);
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 注册用户
     * user_key 用户名
     * password 密码
     *
     */
    public function register(Request $request)
    {
        $this->validate($request, ["user_key" => "required", "password" => "required|alpha_num|alpha_dash|min:6"]);
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
            Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
            return response()->json(["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 修改密码
     * user_id 用户ID
     * oldPassword 旧密码
     * newPassword 新密码
     *
     */
    public function modifyPassword(Request $request)
    {
        $this->validate($request, ["user_id" => "required", "oldPassword" => "required|alpha_num|alpha_dash|min:6", "newPassword" => "required|alpha_num|alpha_dash|min:6"]);
        $user_id = $request->input("user_id", "0000");
        $oldPassword = $request->input("oldPassword", "4321");
        $newPassword = $request->input("newPassword", "1234");
        $accountResult = Account::query()->where([["union_user_id", "=", $user_id], ["account_type", "=", Account::TELEPHONE_LOGIN], ["password", "=", md5($oldPassword)], ["status", "=", Account::NORMAL_STATUS]])->first();
        if (is_null($accountResult)) {
            return response()->json(["error" => "account not exist or password not match"]);
        }
        $accountObj = $accountResult->toArray();
        $accountObj["password"] = md5($newPassword);
        $accountResult->fill($accountObj)->save();
        return response()->json(["account" => $accountObj]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 重置密码
     * telephone 手机号
     * smsCode 验证码
     * newPassword 新密码
     *
     */
    public function resetPassword(Request $request)
    {
        $this->validate($request, ["telephone" => ["required", "regex:/^((\d3)|(\d{3}\-))?13[0-9]\d{8}|15[89]\d{8}|18[0-9]\d{8}/"], "smsCode" => "required", "newPassword" => "required|alpha_num|alpha_dash|min:6"]);
        $telephone = $request->input("telephone", "13800138000");
        $smsCode = $request->input("smsCode", "0000");
        $newPassword = $request->input("newPassword", "1234");
        $smsCodeResult = SmsCode::query()->find($telephone);
        if (is_null($smsCodeResult)) {
            return response()->json(["error" => "smsCode error"]);
        }
        $smsCodeObject = $smsCodeResult->toArray();
        if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["expire_time"]) {
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
        $modifyResult = Account::query()->where([["union_user_id", "=", $accountObj["union_user_id"]], ["account_type", "=", Account::TELEPHONE_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if ($modifyResult) {
            $modifyObj = $modifyResult->toArray();
            $modifyObj["password"] = md5($newPassword);
            $modifyResult->fill($modifyObj)->save();
            return response()->json(["account" => $modifyObj]);
        } else {
            return response()->json(["error" => "account not exist"]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 绑定用户（针对平台用户）
     * user_id 用户ID
     * cp_user_id 平台用户ID
     * cp_id 平台ID
     * sign 签名
     */
    public function attach(Request $request)
    {
        $this->validate($request, ["user_id" => "required", "cp_user_id" => "required", "cp_id" => "required", "sign" => "required"]);
        $user_id = $request->input("user_id", "0000");
        $cp_user_id = $request->input("cp_user_id", "0000");
        $cp_id = $request->input("cp_id", "0000");
        $sign = $request->input("sign", "0000");
        $user = $request->user();
        if (is_null($user)) {
            return response()->json(["error" => "user not exist"]);
        }
        $channelResult = Channel::getQuery()->find($cp_id);
        if (is_null($channelResult)) {
            return response()->json(["error" => "channel not exist"]);
        }
        $channelObj = $channelResult->toArray();
        $signStr = md5($user_id . $cp_user_id . $cp_id . $channelObj["channel_secret"]);
        if ($signStr != $sign) {
            return response()->json(["error" => "sign not match"]);
        }
        $mappingResult = Mapping::getQuery($channelObj["alias"])->updateOrCreate(["channel_id" => $cp_id, "channel_uid" => $cp_user_id, "user_id" => $user_id], ["channel_id" => $cp_id, "channel_uid" => $cp_user_id, "user_id" => $user_id]);
        return response()->json(["mapping" => $mappingResult->toArray()]);
    }

    private function genUserUid()
    {
//        $r = chr(mt_rand(97, 122));
        $r = chr(mt_rand(65, 90));
        $a = $this->charCodeAt($r, 0) - 96;
        $b = rand(0, 1000);
        $c = time();
        return abs($this->leftShift($a, 32) + $this->leftShift($b, 24) + $c);
    }

    private function leftShift($a, $b)
    {
        return pow(2, $b) * $a;
    }

    private function charCodeAt($str, $index)
    {
        $char = mb_substr($str, $index, 1, 'UTF-8');
        if (mb_check_encoding($char, 'UTF-8')) {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            return hexdec(bin2hex($ret));
        } else {
            return null;
        }
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
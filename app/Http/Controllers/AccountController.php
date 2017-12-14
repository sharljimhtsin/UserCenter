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
use App\Lib\Utils;
use App\MaimengAccount;
use App\MaimengUser;
use App\Mapping;
use App\SmsCode;
use App\Token;
use App\User;
use App\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AccountController extends Controller
{

    /**
     * AccountController constructor.
     */
    public function __construct()
    {
        $this->middleware("token", ["only" => ["sendSmsCode", "verifyPhone", "modifyPassword"]]);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function index(Request $request)
    {
        $welcomeResult = Variable::getQuery()->where("name", "=", "welcome")->first();
        if (is_null($welcomeResult)) {
            return Utils::echoContent(Utils::CODE_OK, ["msg" => "欢迎"]);
        }
        $welcomeObj = $welcomeResult->toArray();
        return Utils::echoContent(Utils::CODE_OK, ["msg" => $welcomeObj["value"]]);
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
        Utils::validation($request, ["user_key" => "required", "password" => "required|alpha_num|alpha_dash|min:6"]);
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
                return Utils::echoContent(Utils::CODE_OK, ["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
            } else {
                return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
            }
        } else {
            return Utils::echoContent(Utils::CODE_ACCOUNT_NOT_EXIST_OR_PASSWORD_ERROR);
        }
    }

    /**
     * @param $username
     * @param $password
     * @return array|null|boolean
     *
     * 尝试登录maimeng 账号
     */
    private function tryMaimengAccount($username, $password)
    {
        $accountResult = MaimengAccount::getQuery()->where([["username", "=", $username], ["status", "=", "1"]])->first();
        if (is_null($accountResult)) {
            return null;
        }
        $accountObj = $accountResult->toArray();
        if ($accountObj["password"] != $this->encryptPassword($password)) {
            return false;
        }
        $userResult = MaimengUser::getQuery()->find($accountObj["unionId"]);
        if (is_null($userResult)) {
            return null;
        }
        return [$accountObj, $userResult->toArray()];
    }

    /**
     * @param string $password
     * @return string
     *
     * maimeng 账号密码加密
     */
    private function encryptPassword($password = '')
    {
        if ($password == '') return '';

        $password = trim($password);
        $password = strval($password);
        $password = md5($password);
        return md5($password . '@' . mb_substr($password, 0, 12, 'utf8'));
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
        Utils::validation($request, ["telephone" => ["required", "regex:/^1(1[0-9]|3[0-9]|4[57]|5[0-35-9]|6[6]|7[0135678]|8[0-9]|9[89])\d{8}$/"], "password" => "required|alpha_num|alpha_dash|min:6"]);
        $telephone = $request->input("telephone", "qwerty");
        $password = $request->input("password", "123456");
        $existResult = Account::query()->where([["user_key", "=", $telephone], ["status", "=", Account::NORMAL_STATUS]])->whereIn("account_type", [Account::TELEPHONE_LOGIN, Account::MAIMENG_LOGIN])->exists();
        if (!$existResult) {
            $loginData = $this->tryMaimengAccount($telephone, $password);
            if (is_null($loginData)) {
                return Utils::echoContent(Utils::CODE_ACCOUNT_NOT_EXIST);
            }
            if (is_bool($loginData) && ($loginData == false)) {
                return Utils::echoContent(Utils::CODE_ACCOUNT_PASSWORD_ERROR);
            }
            $accountMaiMeng = $loginData[0];
            $userMaiMeng = $loginData[1];
            $user_id = $this->genUserUid();
            $accountResult = Account::query()->create(["user_key" => $telephone, "password" => md5($password), "account_type" => Account::MAIMENG_LOGIN, "union_user_id" => $user_id, "status" => Account::NORMAL_STATUS]);
            $accountObj = $accountResult->toArray();
            $userResult = User::query()->create(["user_id" => $user_id, "status" => User::NORMAL_STATUS, "role" => User::NORMAL_ROLE]);
            $userObj = $userResult->toArray();
            $userObj["user_id"] = $accountObj["union_user_id"];
            $userObj["nickname"] = $userMaiMeng["nickname"];
            $userObj["telephone"] = $userMaiMeng["telephone"];
            $userObj["avatar"] = $userMaiMeng["avatar"];
            $userObj["birthday"] = $userMaiMeng["birthday"];
            $userObj["sex"] = $userMaiMeng["sex"];
            $userObj["signature"] = $userMaiMeng["signature"];
            $userObj["role"] = User::NORMAL_ROLE;
            $userObj["status"] = User::NORMAL_STATUS;
            $userObj["user_source"] = "maimeng";
            $userResult->fill($userObj);
            $userResult->save();
            $tokenStr = $this->getRandomToken();
            Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
            return Utils::echoContent(Utils::CODE_OK, ["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
        }
        $result = Account::query()->where([["user_key", "=", $telephone], ["password", "=", md5($password)], ["status", "=", Account::NORMAL_STATUS]])->whereIn("account_type", [Account::TELEPHONE_LOGIN, Account::MAIMENG_LOGIN])->first();
        if ($result) {
            $accountObj = $result->toArray();
            $userResult = User::query()->find($accountObj["union_user_id"]);
            if ($userResult) {
                $userObj = $userResult->toArray();
                $tokenStr = $this->getRandomToken();
                Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
                return Utils::echoContent(Utils::CODE_OK, ["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
            } else {
                return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
            }
        } else {
            return Utils::echoContent(Utils::CODE_ACCOUNT_PASSWORD_ERROR);
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
        Utils::validation($request, ["telephone" => ["required", "regex:/^1(1[0-9]|3[0-9]|4[57]|5[0-35-9]|6[6]|7[0135678]|8[0-9]|9[89])\d{8}$/"], "smsCode" => "required"]);
        $telephone = $request->input("telephone", "13800138000");
        $smsCode = $request->input("smsCode", "0000");
        $smsCodeResult = SmsCode::query()->find($telephone);
        if (is_null($smsCodeResult)) {
            return Utils::echoContent(Utils::CODE_SMS_CODE_ERROR);
        }
        $smsCodeObject = $smsCodeResult->toArray();
        if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["expire_time"]) {
            return Utils::echoContent(Utils::CODE_SMS_CODE_INVALID);
        }
        $accountResult = Account::query()->where([["user_key", "=", $telephone], ["account_type", "=", Account::TELEPHONE_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if (is_null($accountResult)) {
            return Utils::echoContent(Utils::CODE_ACCOUNT_NOT_EXIST);
        }
        $accountObj = $accountResult->toArray();
        if (is_null($accountObj["union_user_id"])) {
            return Utils::echoContent(Utils::CODE_ACCOUNT_ERROR);
        }
        $userResult = User::query()->find($accountObj["union_user_id"]);
        if ($userResult) {
            $userObj = $userResult->toArray();
            $tokenStr = $this->getRandomToken();
            Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
            return Utils::echoContent(Utils::CODE_OK, ["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
        } else {
            return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
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
        Utils::validation($request, ["device_id" => "required"]);
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
        return Utils::echoContent(Utils::CODE_OK, ["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
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
        Utils::validation($request, ["telephone" => ["required", "regex:/^1(1[0-9]|3[0-9]|4[57]|5[0-35-9]|6[6]|7[0135678]|8[0-9]|9[89])\d{8}$/"], "user_id" => "required"]);
        $telephone = $request->input("telephone", "13800138000");
        $keyLock = $telephone . "_SmsCd";
        if (Cache::has($keyLock)) {
            return Utils::echoContent(Utils::CODE_SMS_SEND_PER_ONE_MIN);
        } else {
            Cache::put($keyLock, time(), 1);// 1 min CDing
        }
        $user_id = $request->input("user_id", "9138");
        $userResult = User::query()->find($user_id);
        if ($userResult) {
            $userObj = $userResult->toArray();
            $telephoneExist = User::query()->where([["telephone", "=", $telephone], ["user_id", "!=", $user_id]])->count("user_id");
            if ((is_null($userObj["telephone"]) || $telephone == $userObj["telephone"]) && $telephoneExist == 0) {
                $smsCodeStr = $this->genRandomSmsCode();
                SmsCode::query()->updateOrCreate(["telephone" => $telephone], ["telephone" => $telephone, "code" => $smsCodeStr, "expire_time" => $this->getSmsCodeTTLTime()]);
                return Utils::echoContent(Utils::CODE_OK, ["smsCode" => $smsCodeStr]);
            } else {
                return Utils::echoContent(Utils::CODE_TELEPHONE_NOT_MATCH);
            }
        } else {
            return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
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
        Utils::validation($request, ["telephone" => ["required", "regex:/^1(1[0-9]|3[0-9]|4[57]|5[0-35-9]|6[6]|7[0135678]|8[0-9]|9[89])\d{8}$/"]]);
        $telephone = $request->input("telephone", "13800138000");
        $keyLock = $telephone . "_SmsCd";
        if (Cache::has($keyLock)) {
            return Utils::echoContent(Utils::CODE_SMS_SEND_PER_ONE_MIN);
        } else {
            Cache::put($keyLock, time(), 1);// 1 min CDing
        }
        $smsCodeStr = $this->genRandomSmsCode();
        SmsCode::query()->updateOrCreate(["telephone" => $telephone], ["telephone" => $telephone, "code" => $smsCodeStr, "expire_time" => $this->getSmsCodeTTLTime()]);
        return Utils::echoContent(Utils::CODE_OK, ["smsCode" => $smsCodeStr]);
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
        Utils::validation($request, ["telephone" => ["required", "regex:/^1(1[0-9]|3[0-9]|4[57]|5[0-35-9]|6[6]|7[0135678]|8[0-9]|9[89])\d{8}$/"], "smsCode" => "required", "user_id" => "required"]);
        $telephone = $request->input("telephone", "13800138000");
        $smsCode = $request->input("smsCode", "0000");
        $user_id = $request->input("user_id", "9138");
        $userResult = User::query()->find($user_id);
        if ($userResult) {
            $userObj = $userResult->toArray();
            if ($telephone == $userObj["telephone"]) {
                $smsCodeResult = SmsCode::query()->find($telephone);
                if (is_null($smsCodeResult)) {
                    return Utils::echoContent(Utils::CODE_SMS_CODE_ERROR);
                }
                $smsCodeObject = $smsCodeResult->toArray();
                if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["expire_time"]) {
                    return Utils::echoContent(Utils::CODE_SMS_CODE_INVALID);
                }
                // 存入 cache tag 以便下一步操作
                $keyReBind = $user_id . "reBind";
                Cache::put($keyReBind, "1", 1);
                return Utils::echoContent(Utils::CODE_OK);
            } else {
                return Utils::echoContent(Utils::CODE_TELEPHONE_ERROR);
            }
        } else {
            return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
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
            Utils::validation($request, ["telephone" => ["required", "regex:/^1(1[0-9]|3[0-9]|4[57]|5[0-35-9]|6[6]|7[0135678]|8[0-9]|9[89])\d{8}$/"], "smsCode" => "required", "password" => "required|alpha_num|alpha_dash|min:6"]);
//            $telephoneExist = User::query()->where([["telephone", "=", $telephone]])->count("user_id");
//            if ($telephoneExist > 0) {
//                return Utils::echoContent(Utils::CODE_TELEPHONE_EXIST);
//            }
            $smsCodeResult = SmsCode::query()->find($telephone);
            if (is_null($smsCodeResult)) {
                return Utils::echoContent(Utils::CODE_SMS_CODE_ERROR);
            }
            $smsCodeObject = $smsCodeResult->toArray();
            if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["expire_time"]) {
                return Utils::echoContent(Utils::CODE_SMS_CODE_INVALID);
            }
            $result = Account::getQuery()->where([["user_key", "=", $telephone], ["account_type", "=", Account::TELEPHONE_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
            if ($result) {
                $resultObj = $result->toArray();
                $resultObj["password"] = md5($password);
                $result->fill($resultObj)->save();
                unset($resultObj["password"]);
                $userResult = User::getQuery()->find($resultObj["union_user_id"]);
                if (is_null($userResult)) {
                    return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
                }
                $userObj = $userResult->toArray();
                $tokenStr = $this->getRandomToken();
                Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
                return Utils::echoContent(Utils::CODE_OK, ["user" => $userObj, "account" => $resultObj, "token" => $tokenStr]);
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
                return Utils::echoContent(Utils::CODE_OK, ["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
            }
        } else {
            //绑定、换绑手机号
            $keyReBind = $user_id . "reBind";
            if (Cache::has($keyReBind)) {
                $reBind = true;
                Utils::validation($request, ["telephone" => ["required", "regex:/^1(1[0-9]|3[0-9]|4[57]|5[0-35-9]|6[6]|7[0135678]|8[0-9]|9[89])\d{8}$/"], "smsCode" => "required", "user_id" => "required", "token" => "required"]);
            } else {
                $reBind = false;
                Utils::validation($request, ["telephone" => ["required", "regex:/^1(1[0-9]|3[0-9]|4[57]|5[0-35-9]|6[6]|7[0135678]|8[0-9]|9[89])\d{8}$/"], "smsCode" => "required", "password" => "required|alpha_num|alpha_dash|min:6", "user_id" => "required", "token" => "required"]);
            }
            $userResult = User::query()->find($user_id);
            if ($userResult) {
                $tokenResult = Token::query()->find($user_id);
                if ($tokenResult) {
                    $tokenObj = $tokenResult->toArray();
                    if ($token != $tokenObj["token"]) {
                        return Utils::echoContent(Utils::CODE_TOKEN_ERROR);
                    }
                    if (time() > $tokenObj["expire_time"]) {
                        return Utils::echoContent(Utils::CODE_TOKEN_EXPIRED);
                    }
                } else {
                    return Utils::echoContent(Utils::CODE_TOKEN_ERROR);
                }
                $userObj = $userResult->toArray();
                $notBind = is_null($userObj["telephone"]);
                $telephoneExist = User::query()->where([["telephone", "=", $telephone], ["user_id", "!=", $user_id]])->count("user_id");
                if (($notBind || (!$notBind && $reBind)) && $telephoneExist == 0) {
                    $smsCodeResult = SmsCode::query()->find($telephone);
                    if (is_null($smsCodeResult)) {
                        return Utils::echoContent(Utils::CODE_SMS_CODE_ERROR);
                    }
                    $smsCodeObject = $smsCodeResult->toArray();
                    if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["expire_time"]) {
                        return Utils::echoContent(Utils::CODE_SMS_CODE_INVALID);
                    }
                    $userObj["telephone"] = $telephone;
                    $userResult->fill($userObj)->save();
                    if ($notBind) {
                        $accountResult = Account::query()->updateOrCreate(["union_user_id" => $user_id, "account_type" => Account::TELEPHONE_LOGIN, "status" => Account::NORMAL_STATUS], ["user_key" => $telephone, "password" => md5($password)]);
                    } else {
                        $accountResult = Account::query()->updateOrCreate(["union_user_id" => $user_id, "account_type" => Account::TELEPHONE_LOGIN, "status" => Account::NORMAL_STATUS], ["user_key" => $telephone]);
                    }
                    // once telephone bind-ed,disable quick login of it
                    Account::query()->where([["union_user_id", "=", $user_id], ["account_type", "=", Account::TEMP_LOGIN]])->update(["status" => Account::DISABLE_STATUS]);
                    return Utils::echoContent(Utils::CODE_OK, ["account" => $accountResult->toArray(), "user" => $userObj]);
                } else {
                    return Utils::echoContent(Utils::CODE_TELEPHONE_ERROR);
                }
            } else {
                return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
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
        Utils::validation($request, ["user_key" => "required", "password" => "required|alpha_num|alpha_dash|min:6"]);
        $user_key = $request->input("user_key", "qwerty");
        $password = $request->input("password", "123456");
        $result = Account::getQuery()->where([["user_key", "=", $user_key], ["account_type", "=", Account::NORMAL_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if ($result) {
            return Utils::echoContent(Utils::CODE_ACCOUNT_EXIST);
        } else {
            $user_id = $this->genUserUid();
            $accountResult = Account::query()->create(["user_key" => $user_key, "password" => md5($password), "account_type" => Account::NORMAL_LOGIN, "union_user_id" => $user_id, "status" => Account::NORMAL_STATUS]);
            $accountObj = $accountResult->toArray();
            $userResult = User::query()->create(["user_id" => $user_id, "status" => User::NORMAL_STATUS, "role" => User::NORMAL_ROLE]);
            $userObj = $userResult->toArray();
            $tokenStr = $this->getRandomToken();
            Token::query()->updateOrCreate(["user_id" => $userObj["user_id"]], ["user_id" => $userObj["user_id"], "token" => $tokenStr, "expire_time" => $this->getTokenTTLTime()]);
            return Utils::echoContent(Utils::CODE_OK, ["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
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
        Utils::validation($request, ["user_id" => "required", "oldPassword" => "required|alpha_num|alpha_dash|min:6", "newPassword" => "required|alpha_num|alpha_dash|min:6"]);
        $user_id = $request->input("user_id", "0000");
        $oldPassword = $request->input("oldPassword", "4321");
        $newPassword = $request->input("newPassword", "1234");
        $accountResult = Account::query()->where([["union_user_id", "=", $user_id], ["account_type", "=", Account::TELEPHONE_LOGIN], ["password", "=", md5($oldPassword)], ["status", "=", Account::NORMAL_STATUS]])->first();
        if (is_null($accountResult)) {
            return Utils::echoContent(Utils::CODE_ACCOUNT_NOT_EXIST_OR_PASSWORD_ERROR);
        }
        $accountObj = $accountResult->toArray();
        $accountObj["password"] = md5($newPassword);
        $accountResult->fill($accountObj)->save();
        unset($accountObj["password"]);//密码保密
        return Utils::echoContent(Utils::CODE_OK, ["account" => $accountObj]);
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
        Utils::validation($request, ["telephone" => ["required", "regex:/^1(1[0-9]|3[0-9]|4[57]|5[0-35-9]|6[6]|7[0135678]|8[0-9]|9[89])\d{8}$/"], "smsCode" => "required", "newPassword" => "required|alpha_num|alpha_dash|min:6"]);
        $telephone = $request->input("telephone", "13800138000");
        $smsCode = $request->input("smsCode", "0000");
        $newPassword = $request->input("newPassword", "1234");
        $smsCodeResult = SmsCode::query()->find($telephone);
        if (is_null($smsCodeResult)) {
            return Utils::echoContent(Utils::CODE_SMS_CODE_ERROR);
        }
        $smsCodeObject = $smsCodeResult->toArray();
        if ($smsCode != $smsCodeObject["code"] || time() > $smsCodeObject["expire_time"]) {
            return Utils::echoContent(Utils::CODE_SMS_CODE_INVALID);
        }
        $accountResult = Account::query()->where([["user_key", "=", $telephone], ["account_type", "=", Account::TELEPHONE_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if (is_null($accountResult)) {
            $user_id = $this->genUserUid();
            $accountResult = Account::query()->create(["user_key" => $telephone, "password" => md5($newPassword), "account_type" => Account::TELEPHONE_LOGIN, "union_user_id" => $user_id, "status" => Account::NORMAL_STATUS]);
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
            return Utils::echoContent(Utils::CODE_OK, ["token" => $tokenStr, "account" => $accountObj, "user" => $userObj]);
        }
        $accountObj = $accountResult->toArray();
        if (is_null($accountObj["union_user_id"])) {
            return Utils::echoContent(Utils::CODE_ACCOUNT_ERROR);
        }
        $modifyResult = Account::query()->where([["union_user_id", "=", $accountObj["union_user_id"]], ["account_type", "=", Account::TELEPHONE_LOGIN], ["status", "=", Account::NORMAL_STATUS]])->first();
        if ($modifyResult) {
            $modifyObj = $modifyResult->toArray();
            $modifyObj["password"] = md5($newPassword);
            $modifyResult->fill($modifyObj)->save();
            unset($modifyObj["password"]);//密码保密
            $userResult = User::getQuery()->find($accountObj["union_user_id"]);
            if (is_null($userResult)) {
                return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
            }
            return Utils::echoContent(Utils::CODE_OK, ["account" => $modifyObj, "user" => $userResult->toArray()]);
        } else {
            return Utils::echoContent(Utils::CODE_ACCOUNT_NOT_EXIST);
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
        Utils::validation($request, ["user_id" => "required", "cp_user_id" => "required", "cp_id" => "required", "sign" => "required"]);
        $user_id = $request->input("user_id", "0000");
        $cp_user_id = $request->input("cp_user_id", "0000");
        $cp_id = $request->input("cp_id", "0000");
        $sign = $request->input("sign", "0000");
        $user = $request->user();
        if (is_null($user)) {
            return Utils::echoContent(Utils::CODE_USER_NOT_EXIST);
        }
        $channelResult = Channel::getQuery()->find($cp_id);
        if (is_null($channelResult)) {
            return Utils::echoContent(Utils::CODE_CHANNEL_NOT_EXIST);
        }
        $channelObj = $channelResult->toArray();
        $signStr = md5($user_id . $cp_user_id . $cp_id . $channelObj["channel_secret"]);
        if ($signStr != $sign) {
            return Utils::echoContent(Utils::CODE_SIGN_NOT_MATCH);
        }
        $mappingResult = Mapping::getQuery($channelObj["alias"])->updateOrCreate(["channel_id" => $cp_id, "channel_uid" => $cp_user_id, "user_id" => $user_id], ["channel_id" => $cp_id, "channel_uid" => $cp_user_id, "user_id" => $user_id]);
        return Utils::echoContent(Utils::CODE_OK, ["mapping" => $mappingResult->toArray()]);
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
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/7
 * Time: 15:48:02
 */

namespace App\Lib;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;

class Utils
{
    use ProvidesConvenienceMethods;
    const CODE_OK = 0;
    const CODE_USER_NOT_EXIST = 1;
    const CODE_ACCOUNT_NOT_EXIST_OR_PASSWORD_ERROR = 2;
    const CODE_SMS_CODE_ERROR = 3;
    const CODE_SMS_CODE_INVALID = 4;
    const CODE_ACCOUNT_ERROR = 5;
    const CODE_ACCOUNT_NOT_EXIST = 6;
    const CODE_SMS_SEND_PER_ONE_MIN = 7;
    const CODE_TELEPHONE_NOT_MATCH = 8;
    const CODE_TELEPHONE_ERROR = 9;
    const CODE_TELEPHONE_EXIST = 10;
    const CODE_ACCOUNT_EXIST = 11;
    const CODE_TOKEN_ERROR = 12;
    const CODE_TOKEN_EXPIRED = 13;
    const CODE_CHANNEL_NOT_EXIST = 14;
    const CODE_SIGN_NOT_MATCH = 15;
    const CODE_TOKEN_NULL = 16;
    const CODE_USER_ID_NULL = 17;
    const CODE_ACCOUNT_PASSWORD_ERROR = 18;
    const CODE_CHANNEL_EXIST = 19;
    const CODE_NO_DATA = 20;
    const CODE_CHANNEL_ALREADY_APPROVED = 21;
    const CODE_CHANNEL_USER_NOT_EXIST = 22;
    const CODE_CHANNEL_ORDER_ID_DUPLICATED = 23;
    const CODE_CHANNEL_ORDER_ID_NOT_EXIST = 24;
    const CODE_CHANNEL_ORDER_ID_PAID_YET = 25;
    const CODE_VALIDATION_FAIL = 26;


    const CODE_MAP = [
        Utils::CODE_OK => "OK",
        Utils::CODE_USER_NOT_EXIST => "用户不存在",
        Utils::CODE_ACCOUNT_NOT_EXIST_OR_PASSWORD_ERROR => "账号不存在或密码错误",
        Utils::CODE_SMS_CODE_ERROR => "验证码错误",
        Utils::CODE_SMS_CODE_INVALID => "验证码失效",
        Utils::CODE_ACCOUNT_ERROR => "账号错误",
        Utils::CODE_ACCOUNT_NOT_EXIST => "账号不存在",
        Utils::CODE_SMS_SEND_PER_ONE_MIN => "验证码每分钟发送一次",
        Utils::CODE_TELEPHONE_NOT_MATCH => "手机号不匹配",
        Utils::CODE_TELEPHONE_ERROR => "手机号错误",
        Utils::CODE_TELEPHONE_EXIST => "手机号已存在",
        Utils::CODE_ACCOUNT_EXIST => "账号已存在",
        Utils::CODE_TOKEN_ERROR => "token 错误",
        Utils::CODE_TOKEN_EXPIRED => "token 过期",
        Utils::CODE_CHANNEL_NOT_EXIST => "渠道不存在",
        Utils::CODE_SIGN_NOT_MATCH => "签名不匹配",
        Utils::CODE_TOKEN_NULL => "token 为空",
        Utils::CODE_USER_ID_NULL => "user_id 为空",
        Utils::CODE_ACCOUNT_PASSWORD_ERROR => "密码错误",
        Utils::CODE_CHANNEL_EXIST => "渠道已存在",
        Utils::CODE_NO_DATA => "无数据",
        Utils::CODE_CHANNEL_ALREADY_APPROVED => "渠道已经过审",
        Utils::CODE_CHANNEL_USER_NOT_EXIST => "渠道用户不存在",
        Utils::CODE_CHANNEL_ORDER_ID_DUPLICATED => "渠道订单号重复",
        Utils::CODE_CHANNEL_ORDER_ID_NOT_EXIST => "渠道订单号不存在",
        Utils::CODE_CHANNEL_ORDER_ID_PAID_YET => "渠道订单号已支付",
        Utils::CODE_VALIDATION_FAIL => "参数验证出错",
    ];

    static function echoContent($code, $data = null)
    {
        $arr = [];
        $arr["code"] = $code;
        $arr["message"] = array_key_exists($code, Utils::CODE_MAP) ? Utils::CODE_MAP[$code] : "unknown";
        $arr["data"] = $data;
        return response()->json($arr);
    }

    private static $_instance = null;

    static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Utils();
        }
        return self::$_instance;
    }

    const ECHO_MAP = [
        "telephone" => "手机号码格式错误"
    ];

    /**
     * @param Request $request
     * @param $rules
     * @return null
     *
     * 自定义错误验证
     */
    static function validation(Request $request, $rules)
    {
        try {
            Utils::getInstance()->validate($request, $rules);
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $k => $v) {
                if (array_key_exists($k, Utils::ECHO_MAP)) {
                    $errors[$k] = Utils::ECHO_MAP[$k];
                } else {
                    $errors[$k] = $v;
                }
            }
            throw new HttpResponseException(Utils::echoContent(Utils::CODE_VALIDATION_FAIL, $errors));
        }
        return null;
    }
}
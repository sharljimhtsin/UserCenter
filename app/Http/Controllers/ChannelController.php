<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/22
 * Time: 11:00:58
 */

namespace App\Http\Controllers;


use App\Channel;
use App\Lib\Utils;
use App\User;
use Illuminate\Http\Request;

class ChannelController extends Controller
{

    /**
     * ChannelController constructor.
     */
    public function __construct()
    {
        $this->middleware("partner", []);
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
     * @param $prefix
     * @return string
     */
    private function getUniqueStr($prefix)
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
    public function add(Request $request)
    {
        $this->validate($request, ["channel_name" => "required", "pay_callback_url" => "required", "alias" => "required"]);
        $user_id = $request->input("user_id");
        $channel_name = $request->input("channel_name");
        $pay_callback_url = $request->input("pay_callback_url");
        $alias = $request->input("alias", "mysql");
        $result = Channel::getQuery()->where([["channel_name", "=", $channel_name], ["owner", "=", $user_id]])->orWhere("alias", "=", $alias)->first();
        if ($result) {
            return Utils::echoContent(Utils::CODE_CHANNEL_EXIST);
        } else {
            $channel_key = $this->getUniqueStr("K");
            $channel_secret = $this->getUniqueStr("S");
            $addResult = Channel::getQuery()->create(["channel_name" => $channel_name, "channel_key" => $channel_key, "channel_secret" => $channel_secret, "pay_callback_url" => $pay_callback_url, "is_test" => 1, "owner" => $user_id, "alias" => $alias]);
            return Utils::echoContent(Utils::CODE_OK, ["channel" => $addResult->toArray()]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ["channel_id" => "required", "pay_callback_url" => "required"]);
        $user_id = $request->input("user_id");
        $channel_id = $request->input("channel_id");
        $pay_callback_url = $request->input("pay_callback_url");
        $result = Channel::getQuery()->where([["channel_id", "=", $channel_id], ["owner", "=", $user_id]])->first();
        if ($result) {
            $obj = $result->toArray();
            $obj["pay_callback_url"] = $pay_callback_url ? $pay_callback_url : $obj["pay_callback_url"];
            $result->fill($obj)->save();
            return Utils::echoContent(Utils::CODE_OK, ["channel" => $obj]);
        } else {
            return Utils::echoContent(Utils::CODE_CHANNEL_NOT_EXIST);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, ["channel_id" => "required"]);
        $user_id = $request->input("user_id");
        $channel_id = $request->input("channel_id");
        $result = Channel::getQuery()->where([["channel_id", "=", $channel_id], ["owner", "=", $user_id]])->first();
        if ($result) {
            $result->delete();
            return Utils::echoContent(Utils::CODE_OK);
        } else {
            return Utils::echoContent(Utils::CODE_CHANNEL_NOT_EXIST);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        $user_id = $request->input("user_id");
        $result = Channel::getQuery()->where([["owner", "=", $user_id]])->get();
        return Utils::echoContent(Utils::CODE_OK, ["channelList" => $result->toArray()]);
    }
}
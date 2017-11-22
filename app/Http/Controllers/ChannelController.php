<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/22
 * Time: 11:00:58
 */

namespace App\Http\Controllers;


use App\Channel;
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
            return response()->json(["error" => "user not exist"]);
        } else {
            $userObj = $userResult->toArray();
            return response()->json(["user" => $userObj]);
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
        $user_id = $request->input("user_id");
        $channel_name = $request->input("channel_name");
        $pay_callback_url = $request->input("pay_callback_url");
        if (is_null($channel_name) || is_null($pay_callback_url)) {
            return response()->json(["error" => "param error"]);
        }
        $result = Channel::getQuery()->where([["channel_name", "=", $channel_name], ["owner", "=", $user_id]])->first();
        if ($result) {
            return response()->json(["error" => "channel exist"]);
        } else {
            $channel_key = $this->getUniqueStr("K");
            $channel_secret = $this->getUniqueStr("S");
            $addResult = Channel::getQuery()->create(["channel_name" => $channel_name, "channel_key" => $channel_key, "channel_secret" => $channel_secret, "pay_callback_url" => $pay_callback_url, "is_test" => 1, "owner" => $user_id]);
            return response()->json(["channel" => $addResult->toArray()]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user_id = $request->input("user_id");
        $channel_id = $request->input("channel_id");
        $pay_callback_url = $request->input("pay_callback_url");
        if (is_null($channel_id) || is_null($pay_callback_url)) {
            return response()->json(["error" => "param error"]);
        }
        $result = Channel::getQuery()->where([["channel_id", "=", $channel_id], ["owner", "=", $user_id]])->first();
        if ($result) {
            $obj = $result->toArray();
            $obj["pay_callback_url"] = $pay_callback_url ? $pay_callback_url : $obj["pay_callback_url"];
            $result->fill($obj)->save();
            return response()->json(["channel" => $obj]);
        } else {
            return response()->json(["error" => "channel not exist"]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $user_id = $request->input("user_id");
        $channel_id = $request->input("channel_id");
        if (is_null($channel_id)) {
            return response()->json(["error" => "param error"]);
        }
        $result = Channel::getQuery()->where([["channel_id", "=", $channel_id], ["owner", "=", $user_id]])->first();
        if ($result) {
            $result->delete();
            return response()->json(["status" => "ok"]);
        } else {
            return response()->json(["error" => "channel not exist"]);
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
        return response()->json(["channelList" => $result->toArray()]);
    }
}
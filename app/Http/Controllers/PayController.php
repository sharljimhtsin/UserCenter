<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23
 * Time: 10:47:37
 */

namespace App\Http\Controllers;


use App\Channel;
use App\Mapping;
use App\PayOrder;
use Illuminate\Http\Request;

class PayController extends Controller
{

    /**
     * PayController constructor.
     */
    public function __construct()
    {
        $this->middleware("user", ["except" => ["callback"]]);
    }

    public function index(Request $request)
    {

    }

    private function getUniqueID()
    {
        return md5(uniqid(md5(microtime(true)), true));
    }

    public function add(Request $request)
    {
        $user = $request->user();
        if (is_null($user)) {
            return response()->json(["error" => "user_id error"]);
        }
        $user_id = $request->input("user_id");
        $channel_id = $request->input("channel_id");
        $channel_order_id = $request->input("channel_order_id");
        $currency = $request->input("currency", "RMB");
        $extension = $request->input("extension", "");
        $money = $request->input("money");
        $role_id = $request->input("role_id");
        $role_name = $request->input("role_name", "unknown");
        $server_id = $request->input("server_id", "1");
        $server_name = $request->input("server_name", "unknown");
        $product_id = $request->input("product_id");
        $product_name = $request->input("product_name", "unknown");
        $product_desc = $request->input("product_desc", "unknown");
        $notify_url = $request->input("notify_url", "");
        if (is_null($channel_id) || is_null($channel_order_id) || is_null($money) || is_null($role_id) || is_null($product_id)) {
            return response()->json(["error" => "param error"]);
        }
        $channelResult = Channel::getQuery()->find($channel_id);
        if (is_null($channelResult)) {
            return response()->json(["error" => "channel not exist"]);
        }
        $channelObj = $channelResult->toArray();
        $mappingResult = Mapping::getQuery($channelObj["alias"])->where([["channel_id", "=", $channel_id], ["channel_uid", "=", $role_id], ["user_id", "=", $user_id]])->first();
        if (is_null($mappingResult)) {
            return response()->json(["error" => "channel user not exist"]);
        }
        $model = PayOrder::getQuery($channelObj["alias"])->newModelInstance();
        $data = [
            "order_no" => $this->getUniqueID(),
            "channel_id" => $channel_id,
            "channel_order_id" => $channel_order_id,
            "currency" => $currency,
            "extension" => $extension,
            "money" => $money,
            "status" => PayOrder::STATUS_CREATE,
            "user_id" => $user_id,
            "role_id" => $role_id,
            "role_name" => $role_name,
            "server_id" => $server_id,
            "server_name" => $server_name,
            "product_id" => $product_id,
            "product_name" => $product_name,
            "product_desc" => $product_desc,
            "notify_url" => $notify_url
        ];
        $model->fill($data)->save();
        return response()->json(["payOrder" => $data]);
    }

    public function pay(Request $request)
    {

    }

    public function callback(Request $request)
    {

    }
}
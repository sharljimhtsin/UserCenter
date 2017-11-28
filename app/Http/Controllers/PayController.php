<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23
 * Time: 10:47:37
 */

namespace App\Http\Controllers;


use App\Channel;
use App\Lib\Alipay\AlipayTradeService;
use App\Lib\Alipay\AlipayTradeWapPayContentBuilder;
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
        $this->middleware("user", ["except" => ["callbackForWeChat", "callbackForAliPay", "test"]]);
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
        $sign = $request->input("sign", null);
        if (is_null($channel_id) || is_null($channel_order_id) || is_null($money) || is_null($role_id) || is_null($product_id) || is_null($sign)) {
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
        $post = $request->except(["sign"]);
        $serverSign = $this->calcSign($post, $channelObj["channel_secret"]);
        if ($sign != $serverSign) {
            return response()->json(["error" => "sign not match"]);
        }
        $existResult = PayOrder::getQuery($channelObj["alias"])->where([["channel_id", "=", $channel_id], ["channel_order_id", "=", $channel_order_id]])->exists();
        if ($existResult) {
            return response()->json(["error" => "channel order id duplicated"]);
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

    private function calcSign($post, $secretKey)
    {
        ksort($post);
        $str = "";
        foreach ($post as $k => $v) {
            $str .= $k . "=" . $v;
            $str .= "&";
        }
        $str = substr($str, 0, strlen($str) - 1);
        $str .= $secretKey;
        return md5($str);
    }

    const WECHAT = 1;
    const ALIPAY = 2;
    const TENPAY = 3;

    public function pay(Request $request)
    {
        $user = $request->user();
        if (is_null($user)) {
            return response()->json(["error" => "user_id error"]);
        }
        $pay_method = $request->input("pay_method", self::WECHAT);
        $user_id = $request->input("user_id");
        $order_no = $request->input("order_no");
        $channel_id = $request->input("channel_id");
        $channel_order_id = $request->input("channel_order_id");
        $role_id = $request->input("role_id");
        $product_id = $request->input("product_id");
        $sign = $request->input("sign", null);
        if (is_null($channel_id) || is_null($channel_order_id) || is_null($order_no) || is_null($role_id) || is_null($product_id) || is_null($sign)) {
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
        $post = $request->except(["sign"]);
        $serverSign = $this->calcSign($post, $channelObj["channel_secret"]);
        if ($sign != $serverSign) {
            return response()->json(["error" => "sign not match"]);
        }
        $existResult = PayOrder::getQuery($channelObj["alias"])->where([["channel_id", "=", $channel_id], ["channel_order_id", "=", $channel_order_id], ["order_no", "=", $order_no], ["user_id", "=", $user_id]])->first();
        if (is_null($existResult)) {
            return response()->json(["error" => "channel order id not exist"]);
        }
        $existObj = $existResult->toArray();
        if (PayOrder::STATUS_CREATE != $existObj["status"]) {
            return response()->json(["error" => "channel order id paid yet"]);
        }
        if ($channelObj["is_test"] == 1) {
            $existObj["status"] = PayOrder::STATUS_PAYED;
            $existResult->fill($existObj)->save();
            $this->notifyChannel($existObj, $channelObj);
            return response()->json(["msg" => "ok due to sandbox"]);
        } else {
            //TODO alipay wechat etc
            $url = "http://baidu.com";
            switch ($pay_method) {
                case self::WECHAT:
                    $url = $this->buildWeChatUrl($order_no, $product_id, $existObj["product_desc"], $existObj["money"], $channelObj["channel_id"]);
                    break;
                case self::ALIPAY:
                    $url = $this->buildAliPayUrl($existObj["product_desc"], $existObj["product_name"], $order_no, $existObj["money"], $channelObj["channel_id"]);
                    break;
                case self::TENPAY:
                    break;
                default:
                    break;
            }
            return redirect()->to($url);
        }
    }

    /**
     * @param $body
     * @param $subject
     * @param $out_trade_no
     * @param $total_amount
     * @param $arg
     * @return bool|mixed|\SimpleXMLElement|string
     *
     * $aop = new AopClient ();
     * $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
     * $aop->appId = 'your app_id';
     * $aop->rsaPrivateKey = '请填写开发者私钥去头去尾去回车，一行字符串';
     * $aop->alipayrsaPublicKey='请填写支付宝公钥，一行字符串';
     * $aop->apiVersion = '1.0';
     * $aop->postCharset='GBK';
     * $aop->format='json';
     * $aop->signType='RSA2';
     * $request = new AlipayTradeWapPayRequest ();
     * $request->setBizContent("{" .
     * "    \"body\":\"对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。\"," .
     * "    \"subject\":\"大乐透\"," .
     * "    \"out_trade_no\":\"70501111111S001111119\"," .
     * "    \"timeout_express\":\"90m\"," .
     * "    \"total_amount\":9.00," .
     * "    \"product_code\":\"QUICK_WAP_WAY\"" .
     * "  }");
     * $result = $aop->pageExecute ( $request);
     * echo $result;
     */
    private function buildAliPayUrl($body, $subject, $out_trade_no, $total_amount, $arg)
    {
        //超时时间
        $timeout_express = "1m";
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);
        $payRequestBuilder->setPassbackParams($arg);
        $config = config("alipay");
        $payResponse = new AlipayTradeService($config);
        $result = $payResponse->wapPay($payRequestBuilder, $config['return_url'], $config['notify_url']);
        return $result;
    }

    /**
     * @param $out_trade_no
     * @param $product_id
     * @param $body
     * @param $total_fee
     * @param $attach
     *
     * <xml>
     * <appid>wx2421b1c4370ec43b</appid>
     * <attach>支付测试</attach>
     * <body>H5支付测试</body>
     * <mch_id>10000100</mch_id>
     * <nonce_str>1add1a30ac87aa2db72f57a2375d8fec</nonce_str>
     * <notify_url>http://wxpay.wxutil.com/pub_v2/pay/notify.v2.php</notify_url>
     * <openid>oUpF8uMuAJO_M2pxb1Q9zNjWeS6o</openid>
     * <out_trade_no>1415659990</out_trade_no>
     * <spbill_create_ip>14.23.150.211</spbill_create_ip>
     * <total_fee>1</total_fee>
     * <trade_type>MWEB</trade_type>
     * <scene_info>{"h5_info": {"type":"IOS","app_name": "王者荣耀","package_name": "com.tencent.tmgp.sgame"}}</scene_info>
     * <sign>0CB01533B8C1EF103065174F50BCA001</sign>
     * </xml>
     */
    private function buildWeChatUrl($out_trade_no, $product_id, $body, $total_fee, $attach)
    {
        $params = [];
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $params["appid"] = env("WECHAT_APPID", "");
        $params["key"] = env("WECHAT_KEY", "");
        $params["mch_id"] = env("WECHAT_MCH_ID", "");
        $params["device_info"] = "WEB";
        $params["nonce_str"] = $this->getUniqueID();
        $params["sign_type"] = "MD5";
        $params["body"] = $body;
        $params["detail"] = "";
        $params["attach"] = $attach;
        $params["out_trade_no"] = $out_trade_no;
        $params["fee_type"] = "CNY";
        $params["total_fee"] = $total_fee * 100;//订单总金额，单位为分
        $params["spbill_create_ip"] = $this->getClientIp();
        $params["time_start"] = date("yyyyMMddHHmmss");
        $params["time_expire"] = date("yyyyMMddHHmmss", time() + 60 * 60 * 1);
        $params["goods_tag"] = "";
        $params["notify_url"] = env("WECHAT_NOTIFY_URL", "");
        $params["trade_type"] = "MWEB";
        $params["product_id"] = $product_id;
        $params["limit_pay"] = "";
        $params["openid"] = "";
        $json_info = ["h5_info" => ["type" => env("WECHAT_JSON_TYPE"), "wap_url" => env("WECHAT_JSON_URL"), "wap_name" => env("WECHAT_JSON_NAME")]];
        $params["scene_info"] = json_encode($json_info);
        $sign = $this->calcSignForWeChat($params, $params["key"], $params["sign_type"]);
        $params["sign"] = $sign;
        $xml = $this->array_to_xml($params, new \SimpleXMLElement('<?xml version="1.0"?><data></data>'));
        $result = $this->doRequest($url, $xml->asXML(), 1, 1);
        $xmlObj = simplexml_load_string($result);
        var_dump($xmlObj);
    }

    private function array_to_xml(array $arr, \SimpleXMLElement $xml)
    {
        foreach ($arr as $k => $v) {
            is_array($v)
                ? $this->array_to_xml($v, $xml->addChild($k))
                : $xml->addChild($k, $v);
        }
        return $xml;
    }

    private function getClientIp()
    {
        $cip = "unknown";
        if ($_SERVER["REMOTE_ADDR"]) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        }
        return $cip;
    }

    private function calcSignForWeChat($data, $key, $type = "MD5")
    {
        ksort($data);
        $str = "";
        foreach ($data as $k => $v) {
            $v = trim($v);
            if (empty($v) || "sign" == $k) {
                continue;
            }
            $str .= ($k . "=" . $v . "&");
        }
        $str .= ("key" . "=" . $key);
        if ($type == "MD5") {
            return strtoupper(md5($str));
        } else {
            return strtoupper(hash_hmac("sha256", $str, $key));
        }
    }


    private function doRequest($url, $post, $isPost = 1, $isRaw = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $isPost);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $isRaw ? $post : http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function notifyChannel($orderObj, $channelObj)
    {
        $url = $channelObj["pay_callback_url"];
        $channel_secret = $channelObj["channel_secret"];
        $alias = $channelObj["alias"];
        $sign = $this->calcSign($orderObj, $channel_secret);
        $orderObj["sign"] = $sign;
        $response = $this->doRequest($url, $orderObj, 1);
        if ("OK" == $response) {
            PayOrder::getQuery($alias)->find($orderObj["order_id"])->update(["status" => PayOrder::STATUS_COMPLETED]);
            return true;
        } else {
            //LOG
            return false;
        }
    }

    /**
     * @param Request $request
     * @return string
     *
     * <xml>
     * <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
     * <attach><![CDATA[支付测试]]></attach>
     * <bank_type><![CDATA[CFT]]></bank_type>
     * <fee_type><![CDATA[CNY]]></fee_type>
     * <is_subscribe><![CDATA[Y]]></is_subscribe>
     * <mch_id><![CDATA[10000100]]></mch_id>
     * <nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>
     * <openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid>
     * <out_trade_no><![CDATA[1409811653]]></out_trade_no>
     * <result_code><![CDATA[SUCCESS]]></result_code>
     * <return_code><![CDATA[SUCCESS]]></return_code>
     * <sign><![CDATA[B552ED6B279343CB493C5DD0D78AB241]]></sign>
     * <sub_mch_id><![CDATA[10000100]]></sub_mch_id>
     * <time_end><![CDATA[20140903131540]]></time_end>
     * <total_fee>1</total_fee>
     * <trade_type><![CDATA[JSAPI]]></trade_type>
     * <transaction_id><![CDATA[1004400740201409030005092168]]></transaction_id>
     * </xml>
     */
    public function callbackForWeChat(Request $request)
    {
        $xmlStr = $request->getContent();
        $xmlObj = simplexml_load_string($xmlStr, "SimpleXMLElement", LIBXML_NOCDATA);
        $jsonObj = json_decode(json_encode($xmlObj), JSON_OBJECT_AS_ARRAY);
        $result = ["return_code" => "FAIL", "return_msg" => "ERROR"];
        if (trim($jsonObj["return_code"]) != "SUCCESS") {
            $xml = $this->array_to_xml($result, new \SimpleXMLElement('<?xml version="1.0"?><data></data>'));
            return response($xml->asXML());
        }
        $key = env("WECHAT_KEY", "");
        $signStr = $this->calcSignForWeChat($jsonObj, $key, trim($jsonObj["sign_type"]));
        if ($signStr != trim($jsonObj["sign"])) {
            $result["return_msg"] = "SIGN NOT MATCH";
            $xml = $this->array_to_xml($result, new \SimpleXMLElement('<?xml version="1.0"?><data></data>'));
            return response($xml->asXML());
        }
        $channel_id = trim($jsonObj["attach"]);
        $channelResult = Channel::getQuery()->find($channel_id);
        if (is_null($channelResult)) {
            $result["return_msg"] = "DATA NULL";
            $xml = $this->array_to_xml($result, new \SimpleXMLElement('<?xml version="1.0"?><data></data>'));
            return response($xml->asXML());
        }
        $channelObj = $channelResult->toArray();
        $out_trade_no = trim($jsonObj["out_trade_no"]);
        $orderResult = PayOrder::getQuery($channelObj["alias"])->where([["order_no", "=", $out_trade_no], ["channel_id", "=", $channel_id]])->first();
        if (is_null($orderResult)) {
            $result["return_msg"] = "ORDER NULL";
            $xml = $this->array_to_xml($result, new \SimpleXMLElement('<?xml version="1.0"?><data></data>'));
            return response($xml->asXML());
        }
        $orderObj = $orderResult->toArray();
        if ($orderObj["status"] != PayOrder::STATUS_CREATE) {
            $result["return_msg"] = "ORDER ERROR";
            $xml = $this->array_to_xml($result, new \SimpleXMLElement('<?xml version="1.0"?><data></data>'));
            return response($xml->asXML());
        }
        if (intval(trim($jsonObj["total_fee"])) != intval($orderObj["money"]) * 100) {//订单总金额，单位为分
            $result["return_msg"] = "MONEY ERROR";
            $xml = $this->array_to_xml($result, new \SimpleXMLElement('<?xml version="1.0"?><data></data>'));
            return response($xml->asXML());
        }
        $orderObj["status"] = PayOrder::STATUS_PAYED;
        $orderResult->fill($orderObj)->save();
        $this->notifyChannel($orderObj, $channelObj);
        $result["return_code"] = "SUCCESS";
        $result["return_msg"] = "";
        $xml = $this->array_to_xml($result, new \SimpleXMLElement('<?xml version="1.0"?><data></data>'));
        return response($xml->asXML());
    }

    /**
     * @param Request $request
     *
     * https://api.xx.com/receive_notify.htm?total_amount=2.00&buyer_id=2088102116773037&body=大乐透2.1&trade_no=2016071921001003030200089909&refund_fee=0.00¬ify_time=2016-07-19 14:10:49&subject=大乐透2.1&sign_type=RSA2&charset=utf-8¬ify_type=trade_status_sync&out_trade_no=0719141034-6418&gmt_close=2016-07-19 14:10:46&gmt_payment=2016-07-19 14:10:47&trade_status=TRADE_SUCCESS&version=1.0&sign=kPbQIjX+xQc8F0/A6/AocELIjhhZnGbcBN6G4MM/HmfWL4ZiHM6fWl5NQhzXJusaklZ1LFuMo+lHQUELAYeugH8LYFvxnNajOvZhuxNFbN2LhF0l/KL8ANtj8oyPM4NN7Qft2kWJTDJUpQOzCzNnV9hDxh5AaT9FPqRS6ZKxnzM=&gmt_create=2016-07-19 14:10:44&app_id=2015102700040153&seller_id=2088102119685838¬ify_id=4a91b7a78a503640467525113fb7d8bg8e
     */
    public function callbackForAliPay(Request $request)
    {
        $arr = $request->all();
        $config = config("alipay");
        $aliPayService = new AlipayTradeService($config);
        $result = $aliPayService->check($arr);
    }

    public function test(Request $request)
    {
        $url = $this->buildAliPayUrl("a", "s", "c", "c", "d");
        return response($url);
    }
}
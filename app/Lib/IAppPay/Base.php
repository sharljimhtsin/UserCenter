<?php
/**
 *功能：爱贝云计费接口公用函数
 *详细：该页面是请求、通知返回两个文件所调用的公用函数核心处理文件
 *版本：1.0
 *修改日期：2014-06-26
 * '说明：
 * '以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己的需要，按照技术文档编写,并非一定要使用该代码。
 * '该代码仅供学习和研究爱贝云计费接口使用，只是提供一个参考。
 */


namespace App\Lib\IAppPay;

class Base
{
    function formatPubKey($pubKey)
    {
        $fKey = "-----BEGIN PUBLIC KEY-----\n";
        $len = strlen($pubKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($pubKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END PUBLIC KEY-----";
        return $fKey;
    }

    function formatPriKey($priKey)
    {
        $fKey = "-----BEGIN RSA PRIVATE KEY-----\n";
        $len = strlen($priKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($priKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END RSA PRIVATE KEY-----";
        return $fKey;
    }

    function sign($data, $priKey)
    {
        //转换为openssl密钥
        $res = openssl_get_privatekey($priKey);

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    function verify($data, $sign, $pubKey)
    {
        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);

        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //返回资源是否成功
        return $result;
    }

    function parseResp($content, $pkey, &$respJson)
    {
        $arr = array_map(create_function('$v', 'return explode("=", $v);'), explode('&', $content));
        $resp = [];
        foreach ($arr as $value) {
            $resp[($value[0])] = $value[1];
        }

        //解析transdata
        if (array_key_exists("transdata", $resp)) {
            $respJson = json_decode($resp["transdata"]);
        } else {
            return FALSE;
        }

        //验证签名，失败应答报文没有sign，跳过验签
        if (array_key_exists("sign", $resp)) {
            //校验签名
            $pkey = $this->formatPubKey($pkey);
            return $this->verify($resp["transdata"], $resp["sign"], $pkey);
        } else if (array_key_exists("errmsg", $respJson)) {
            return FALSE;
        }

        return TRUE;
    }

    function request_by_curl($remoteServer, $postData, $userAgent)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remoteServer);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        $data = urldecode(curl_exec($ch));
        curl_close($ch);

        return $data;
    }

    public function composeReq($reqJson, $vkey)
    {
        //获取待签名字符串
        $content = json_encode($reqJson);
        //格式化key，建议将格式化后的key保存，直接调用
        $vkey = $this->formatPriKey($vkey);

        //生成签名
        $sign = $this->sign($content, $vkey);

        //组装请求报文，目前签名方式只支持RSA这一种
        $reqData = "transdata=" . urlencode($content) . "&sign=" . urlencode($sign) . "&signtype=RSA";

        return $reqData;
    }

//H5请求报文拼接
    function h5composeReq($reqJson, $vkey)
    {
        //获取待签名字符串
        $content = json_encode($reqJson);
        //格式化key，建议将格式化后的key保存，直接调用
        $vkey = $this->formatPriKey($vkey);

        //生成签名
        $sign = $this->sign($content, $vkey);

        //组装请求报文，目前签名方式只支持RSA这一种
        $reqData = "data=" . urlencode($content) . "&sign=" . urlencode($sign) . "&sign_type=RSA";

        return $reqData;
    }


    //发送post请求 ，并得到响应数据  和对数据进行验签
    function HttpPost($Url, $reqData)
    {
        $respData = $this->request_by_curl($Url, $reqData, " demo ");
        if (!$this->parseResp($respData, Config::platpkey, $notifyJson)) {
            echo "fail";
        }
        echo "TEST respData:$respData\n";
    }

    //在使用H5 Iframe版本时 生成签名数据  次函数只适用于H5  Iframe版本支付。
    function H5IframeSign($transid, $redirecturl, $cpurl, $appkey)
    {
        $content = trim($transid) . '' . trim($redirecturl) . '' . trim($cpurl);//拼接$transid   $redirecturl    $cpurl
        $appkey = $this->formatPriKey($appkey);
        $sign = $this->sign($content, $appkey);
        return $sign;
    }

    /*
     * 此demo 代码 使用于 cp 通过主动查询方式 获取同步数据。
     * 请求地址见文档
     * 请求方式:post
     * 流程：cp服务端组装请求参数并对参数签名，以post方式提交请求并获得响应数据，处理得到的响应数据，调用验签函数对数据验签。
     * 请求参数及请求参数格式：transdata={"appid":"123456","cporderid":"3213213"}&sign=xxxxxx&signtype=RSA
     * 注意：只有在客户端支付成功的订单，主动查询才会有交易数据。
     * 以下实现 各项请求参数 处理代码：
     *
     * */
    function ReqData()
    {
        //数据现组装成：{"appid":"12313","logintoken":"aewrasera98seuta98e"}
        $contentdata["appid"] = "3002495803";
        $contentdata["cporderid"] = "55e37ac2c0dc98972475d640";
        //组装请求报文 格式：$reqData="transdata={"appid":"123","logintoken":"3213213"}&sign=xxxxxx&signtype=RSA"
        $reqData = $this->composeReq($contentdata, Config::appkey);
        $this->HttpPost(Config::queryResultUrl, $reqData);
    }

    /**
     * 此demo 代码 使用于 cp 通过主动查询方式 获取商品契约。
     * 请求地址见文档
     * 请求方式:post
     * 流程：cp服务端组装请求参数并对参数签名，以post方式提交请求并获得响应数据，处理得到的响应数据，调用验签函数对数据验签。
     * 请求参数及请求参数格式：transdata={"appid":"500000185","appuserid":"A100003A832D40"}&sign=VvT9gHqGjwuhj07/lbcErBo6b23tX1Z5f/aiBItCw5YlFZb6MQpg/NLc9SCA6qc+S6Pw+Jqe87QiiWpXhPf1fEIclLdu5vWmbFMvA4VMW+Il+6oTJFuJItjfIfhGhljEIrgqXO5ZrNs8mrbKBkJHjUtHv1jRFzFtCQZeMgwZr3U=&signtype=RSA
     * 以下实现 各项请求参数 处理代码：
     */
    function ReqData2()
    {
        //数据现组装成：{"appid":"12313","logintoken":"aewrasera98seuta98e"}
        $contentdata["appid"] = "3002495803";
        $contentdata["appuserid"] = "55e37ac2c0dc98972475d640";
        //组装请求报文 格式：$reqData="transdata={"appid":"123","appuserid":"3213213"}&sign=xxxxxx&signtype=RSA"
        $reqData = $this->composeReq($contentdata, Config::appkey);
        $this->HttpPost(Config::querysubsUrl, $reqData);
    }

    /**
     * 此demo 代码 使用于 用户可以对已经完成购买的契约进行退订，退订时会将契约置为退订状态，在该状态下用户仍然可以使用该商品，直到契约失效，但是不再进行自动续费。
     * 请求地址见文档
     * 请求方式:post
     * 流程：cp服务端组装请求参数并对参数签名，以post方式提交请求并获得响应数据，处理得到的响应数据，调用验签函数对数据验签。
     * 请求参数及请求参数格式：transdata={"appid":"500000185","appuserid":"A100003A832D40","waresid":1}&sign=VvT9gHqGjwuhj07/lbcErBo6b23tX1Z5f/aiBItCw5YlFZb6MQpg/NLc9SCA6qc+S6Pw+Jqe87QiiWpXhPf1fEIclLdu5vWmbFMvA4VMW+Il+6oTJFuJItjfIfhGhljEIrgqXO5ZrNs8mrbKBkJHjUtHv1jRFzFtCQZeMgwZr3U=&signtype=RSA
     * 以下实现 各项请求参数 处理代码：
     */
    function ReqData3()
    {
        //数据现组装成：{"appid":"12313","appuserid":"aewrasera98seuta98e"}
        $contentdata["appid"] = "3002495803";
        $contentdata["appuserid"] = "55e37ac2c0dc98972475d640";
        $contentdata["waresid"] = 1;
        //组装请求报文 格式：$reqData="transdata={"appid":"500000185","appuserid":"A100003A832D40","waresid":1}&sign=xxxxxx&signtype=RSA"
        $reqData = $this->composeReq($contentdata, Config::appkey);
        $this->HttpPost(Config::subcancel, $reqData);
    }

    /**
     * @param $appid
     * @param $appuserid
     * @param $waresid
     *
     * 此demo 代码 使用于 用户契约鉴权。
     * 请求地址见文档
     * 请求方式:post
     * 流程：cp服务端组装请求参数并对参数签名，以post方式提交请求并获得响应数据，处理得到的响应数据，调用验签函数对数据验签。
     * 请求参数及请求参数格式：transdata={"appid":"500000185","appuserid":"A100003A832D40","waresid":1}&sign=N85bxusvUozqF3iwfAq3Ts3UeyZn8mKi5BVe+H+Vg1nrcE06AhHt7IrJLO3I5njZSF4g5CbLMLiTJiXCmNsH/t35gU3bmIKFPKiw7g3aq0hMofyhgsCLXSWEOrSIa7W6mLzPcEhUkjdX9XxsASbsILHTrJwZYYG7d9PTyhqSmoA=&signtype=RSA
     * 以下实现 各项请求参数 处理代码：
     */
    function ReqData4($appid, $appuserid, $waresid)
    {
        //组装参数json格式：
        $contentdata["appid"] = $appid;
        $contentdata["appuserid"] = $appuserid;
        $contentdata["waresid"] = $waresid;
        //调用函数组装json格式，并且对数据进行签名，最终组装请求参数   如：：transdata={"appid":"500000185","appuserid":"A100003A832D40","waresid":4}&sign=N85bxusvUozqF3iwfAq3Ts3UeyZn8mKi5BVe+H+Vg1nrcE06AhHt7IrJLO3I5njZSF4g5CbLMLiTJiXCmNsH/t35gU3bmIKFPKiw7g3aq0hMofyhgsCLXSWEOrSIa7W6mLzPcEhUkjdX9XxsASbsILHTrJwZYYG7d9PTyhqSmoA=&signtype=RSA
        $reqData = $this->composeReq($contentdata, Config::appkey);
        $this->HttpPost(Config::ContractAuthenticationUrl, $reqData);
    }
}
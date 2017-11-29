<?php
/**
 * qpayMachAPI.php 业务调用方可做二次封装
 * Created by HelloWorld
 * vers: v1.0.0
 * User: Tencent.com
 */

namespace App\Lib\Tenpay;

require_once('qpayMchUtil.class.php');

class QpayMchAPI
{
    protected $url;
    protected $isSSL;
    protected $timeout;

    public function __construct($url, $isSSL, $timeout = 5)
    {
        $this->url = $url;
        $this->isSSL = $isSSL;
        $this->timeout = $timeout;
    }

    public function reqQpay($params)
    {
        $ret = array();
        //商户号
        $params["mch_id"] = QpayMchConf::MCH_ID;
        //随机字符串
        $params["nonce_str"] = QpayMchUtil::createNoncestr();
        //签名
        $params["sign"] = QpayMchUtil::getSign($params);
        //notify url
        $params["notify_url"] = QpayMchConf::NOTIFY_URL;
        //生成xml
        $xml = QpayMchUtil::arrayToXml($params);

        if (isset($this->isSSL)) {
            $ret = QpayMchUtil::reqByCurlSSLPost($xml, $this->url, $this->timeout);
        } else {
            $ret = QpayMchUtil::reqByCurlNormalPost($xml, $this->url, $this->timeout);
        }
        return $ret;
    }

}
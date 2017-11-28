<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/28
 * Time: 12:18:30
 */

namespace App\config;


return [
    //应用ID,您的APPID。
    'app_id' => "11213123",

    //商户私钥，您的原始格式RSA私钥
    'merchant_private_key' => "MIICXAIBAAKBgQDEqG5Qq1zKHvwb1Ox4J7dPFZul7GCCP0dTaqBp7kFR6E5avwvuhAiazbYGx96txSa9wCWkzDDvolICScE4SaAjBf7hbJjJmfhL4qrAv8GWz5u3rVWDblvsg0VkoBY7/5Mf3x9w3hFJkMKpIScOVSfDBDxWf+B8DGA2H6fvFiD9MwIDAQABAoGAP0ISoh5NLbMD04wNOKVF4MmJlLjRXnQuZFXDHfAG0OsR8TzNAL816V3MdKfsKCdny9e4BCeughbLnHLPFWURfkHZXSUqzF6PeQS3CORkPrwNbOOWyd/R5HwfXP6uBquDq9gb3eDWJnMDpACQi5TAni/zbgh3Q2t1l7LIGuWsV4kCQQDxztKgLyBcxrzwXFftATjAGxvbQaRyG3sW4/jtRUUOqq53pQ9mqgqRRCrfCLBuowNCyt/T0OlwLXHJhezs0Tg9AkEA0DM6NJ4zOQrZfnetpfCTfStr+Xm56mQqeuTfx2x74g1a9AtNlCi6KCNFHraY//s5HLtI8MD35DW9frs9oabyLwJAbZ6DIZb7ptN5p8VVHt5k6cHgWP9jG0+V94SVvoqeic2alia/2pzPeZdbkAySXzWLLuZlndKhYPdZFDCgfaNDSQJBAIVgS+lpb2cbjDl4ccXcWJ/XMVSgpnmBsbUI6lLXLIWkCKBOnWRMsvUDo0QJtfpG9k9xq0iQVj3cL4kAanskeAECQAUu7Y9bb0i/xS9tkva8fZTr8j8eHZ2U3A/IRXO8OoG0v1JKg1cRS6KpWbmfeN9PF8iAbcpKFe33Y8WM60sYMN8=",

    //异步通知地址
    'notify_url' => "http://工程公网访问地址/alipay.trade.wap.pay-PHP-UTF-8/notify_url.php",

    //同步跳转
    'return_url' => "http://mitsein.com/alipay.trade.wap.pay-PHP-UTF-8/return_url.php",

    //编码格式
    'charset' => "UTF-8",

    //签名方式
    'sign_type' => "RSA2",

    //支付宝网关
    'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key' => "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDEqG5Qq1zKHvwb1Ox4J7dPFZul7GCCP0dTaqBp7kFR6E5avwvuhAiazbYGx96txSa9wCWkzDDvolICScE4SaAjBf7hbJjJmfhL4qrAv8GWz5u3rVWDblvsg0VkoBY7/5Mf3x9w3hFJkMKpIScOVSfDBDxWf+B8DGA2H6fvFiD9MwIDAQAB"
];
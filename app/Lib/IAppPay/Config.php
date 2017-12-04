<?php

/**
 *功能：配置文件
 *版本：1.0
 *修改日期：2014-06-26
 * '说明：
 * '以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己的需要，按照技术文档编写,并非一定要使用该代码。
 * '该代码仅供学习和研究爱贝云计费接口使用，只是提供一个参考。
 */

namespace App\Lib\IAppPay;

class Config
{
    //爱贝商户后台接入url
    const iapppayCpUrl = "http://ipay.iapppay.com:9999";
//登录令牌认证接口 url
    const tokenCheckUrl = self::iapppayCpUrl . "/openid/openidcheck";

//下单接口 url
// $orderUrl=$coolyunCpUrl . "/payapi/order";
    const orderUrl = self::iapppayCpUrl . "/payapi/order";

//支付结果查询接口 url
    const queryResultUrl = self::iapppayCpUrl . "/payapi/queryresult";

//契约查询接口url
    const querysubsUrl = self::iapppayCpUrl . "/payapi/subsquery";

//契约鉴权接口Url
    const ContractAuthenticationUrl = self::iapppayCpUrl . "/payapi/subsauth";

//取消契约接口Url
    const subcancel = self::iapppayCpUrl . "/payapi/subcancel";
//H5和PC跳转版支付接口Url
    const h5url = "https://web.iapppay.com/h5/gateway?";
    const pcurl = "https://web.iapppay.com/pc/gateway?";

//应用编号
    const appid = "3002495803";
//应用私钥
    const appkey = "MIICXgIBAAKBgQDZjpotEmPKwRPKeqb6kqLgmiJkmUQ6EgMAlHBLCAdykb8mAKNWPFs+uyyknOAg+kqQzS9oEoF1P2YYDzYLVQeU4x2c9PtLPMxhXOqdiS6tdJ7RBa2SS4z0WaPmjGAds8qvVnec8Kp5/UXpIDXHRfJ7vNCwNB5O1BEJO/uIRMNIOwIDAQABAoGBAM/HCOpo+NPIyN0FfPotF8/IhXZshqOrViC0o/aU6X/7QILL8zNGG6Ly4nUouknkoVhgDpmnqupOrXPm+yehgsVljoeYRDVEmoyPvFNm+lbv5iDsnHlOohkyEdIO8tMYX7FT269YjWd7PGV1cnHqYUUAq0ncxbu2/RbPbXXsWcAJAkEA83tyOnvKMm46wEaLYXbUzS2iWCv+X0/4h5IKU9q9D2kYhApGaPIOWKf7qtLigkwvSwZ6ZU4kPKcmM817WR64vwJBAOS98iTBD6tYMe6U0Y5E1rO/sQQrIpMwJ2Y6PJQ/uU8z6rXsbEztUR/pbEExkf2a0xCByRxPEwrIRgzBat2Q84UCQHvAvLhY/tZPDHF56ZHqMhLvJNqn0axkGy/c3H7uaLWSdzF1f4ALt5r8FoAmm5YaXtdFPaSL6QMi+dnOkOklIkUCQQCFt0AhGjb1vCXcSWTDHRzBkRKC1FBu6JxvlyWoqCPE2B2h4aZhxe1BkWu2JKsqLGKr6KLPCK6iA/dnJ344La8dAkEA71Mz9WB4fhhU3e284lM1Y6ybS+F5a04racip9NbRAp9bnaDXDBO/EeFM4JZtb48IgnIJ0hfe09y+MDFgw4tgFA==";
//平台公钥
    const platpkey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDoEQasj75Yi/MWc0ZF0+ZiBmguwSegyr5Z+N5A93VXG7SSA4OSb/eaH5FgCsW25cGxRwfpwl9jamiG4R/2vM72cQFQX5lEK0JkcHd/gxhhGFwGg7snFJpYCxYCflN0WHxrR47rijC7ipzRp+Noq50AMCF79AMaPIRSTCy9gLPpwQIDAQAB";
}
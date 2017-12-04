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
    const appid = "3016844758";
//应用私钥
    const appkey = "MIICXQIBAAKBgQCQlZ3WpVnBVqPtHYPNSgTh2lAM173AIPA3ZqgBTO/tS5z3hUFNW/RRpyWi2/EWydDFas1RE8+oqYTQbBp0vaj9nVwJsj6juzTZKHBK4TzZBB062z83Ndn//3v8Xsm0qDnan6CB6qKykOBnZNySA6EX4c/VKqgRXXIkof+vYYG3jwIDAQABAoGAZk9pFGWHQNd1Qim7hX4WPFeGg7/6RsVIFnvu8JSnxqvV1BUYY4xpM4pqiHbIgA6pS7lKtk+lhz0FfZKxmeBHfrWDPl2B2A/UtZlZXjcdVAfwla33D37IJDEvkfEJhnkd7ijcNkaQFiG6AA/MxKmLgmDH7tzm4dE2jUtRz3NpmiECQQDWdounoEa0DnJ7Dql295uJrZ3UVAS552icnSFh6BG0IEtQgRA1lK/xlkQTZU7uCSYkKjdu5TBtswj4kNJepYofAkEArJZdwrjftEr28Tgv6UsL7s9Niz7Ji7W4VxBo4bEWxQVue0QUwlQvVkXiDzDrwLgiEv+xBDcp3nuWZibvqaoEkQJAOWyMgFm+FMA5jxGh9qTeFMNUG2JT0aNQs36vTZPiCaUMBYZJXM9vPPTLTZY2yC3S9KQJK7xad0UVBdlhOs6AaQJBAJe4VFKTDW3EnRPvjgbrx2C38vZfSvS72oMHVPxbHqYzIS3R6uHu+fJA2vr5ybaQ6LuE9gu7pt4EDXJd9kBTy1ECQQDSUtd0LxrpaEJJgWM7Qb8EJTWw/t/Ut3IOVSGLdkhfIrpbNsIFqme5vi/7rW0TozJiLFNFrYQPVacafe/bcn1s";
//平台公钥
    const platpkey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDJT2GCpIKzJptlR3ecE8ZLm/IzMLklAZBSCTfcG6ugX1jhCvM67zVCjhzDanYLFlDCYxoBjpYOsYbDdjLmTUn6US1JVVrPfj0hei/XRT1S5qqE5fZnktkKNjWB6Oxz4eL0B9d4RfglkjYTJ0AE0dKryspfve7MsydwhiJW/tWJdQIDAQAB";
}
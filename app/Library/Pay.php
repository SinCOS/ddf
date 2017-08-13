<?php 


namespace App\Library;

use App\Library\DES3;

/**
* 
*/
class Pay 
{
	static $isTest = false;
    static $version = "2.1";
    static $testURL, $partnerId, $signKey, $merChantId, $encKey;
    public function __construct(){

    }
	public static function init()
    {
        if (Pay::$isTest) {
            /**
             * @var use for test
             */
            Pay::$testURL = "http://mtest.dianpayer.com:8088/gateway.do";
            Pay::$partnerId = "111222333444555";
            Pay::$signKey = "99988888812121255544";
            Pay::$encKey = "111222333444555666777888";
            Pay::$merChantId = "99911010019";
        } else {
            /**
             * @var use for server
             */
            Pay::$testURL = "https://app.dianpayer.com/gateway.do";
            Pay::$partnerId = "900033500006583";
            Pay::$signKey = "bf5145d69ca71e27fa92";
            Pay::$encKey = "99e9840b3b08e8b428dfdcbc";
            Pay::$merChantId = "900033500006583";
        }
    }
    public static function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    public static function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

     /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url 请求URL
     * @param  array $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @param  string &$result    响应数据->接收结果
     * @param  int  statusCode  http返回码
     */
    public static function http($url, $params, $method = 'GET', $header = array(), $multi = false, &$result)
    {
        $opts = array(CURLOPT_TIMEOUT => 30, CURLOPT_RETURNTRANSFER => 1, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_HTTPHEADER => $header);
        /* 根据请求类型设置特定参数 */
        $ch = null;
        switch (strtoupper($method)) {
            case 'GET' :
                $ch = curl_init($url . '&' . http_build_query($params));
                break;
            case 'POST' :
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $ch = curl_init($url);
                //curl_setopt($ch, CURLOPT_POST, 1);
                //curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default :
                throw new Exception('不支持的请求方式！');
        }
        /* 初始化并执行curl请求 */
        foreach ($opts as $key => $value) {
           @curl_setopt($ch, $key, $value);
        }
        @curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($error || @curl_errno($ch)) {
            $code = -1;
        }
        return $code;

    }
     /**
     * @param string $url 服务器地址
     * @param string $parnerId 商户点付提供
     * @param string $servieType 方法名称
     * @param array $request 请求参数
     * @param string $result 请求结果
     * @param int $type 交易类型
     * @return int|mixed http返回码
     * @throws Exception
     */
    public static function request($url, $parnerId, $servieType, $request, &$result, $type = 0)
    {
        $params = array();
        $params["random"] = Pay::getRandChar(5);
        $params["version"] = Pay::$version;
        $params["encoding"] = "utf-8";

        if ($type == 1) {
            $params["errMsg"] = "通知交易";
            $params["respCode"] = "00";
        }
        $params["partnerId"] = $parnerId;
        $params["service_type"] = $servieType;
        $jsonString = json_encode($request, JSON_UNESCAPED_UNICODE);
        echo "\njson---->" . $jsonString . "\n";


        $desMethod = new DES3(Pay::$encKey);
        $encodeParams = $desMethod->encrypt($jsonString);

        if ($type == 1) {
            $params["result_json"] = $encodeParams;
        } else {
            $params["request_json"] = $encodeParams;
        }
        $params["sign"] = self::getSignData($params, Pay::$signKey);

        return Pay::http($url, $params, 'POST', null, false, $result);
    }

    public static function getSignData($params, $signKey)
    {
        $signString = "";
        ksort($params);
        foreach ($params as $key => $key_value) {
            if ($key != "sign") {
                $signString = $signString . $key . "=" . $key_value . "&";
            }
        }
        $signString = $signString . $signKey;
        return md5($signString);
    }
    public static function parseResult($result,$urldeocde = false){
        $urlStr = explode('&',$result);
        $resultStr = null;
        $resultReson = [];
        foreach($urlStr as $param){
            $var = explode('=',$param);
        	$resultReson[$var[0]] = $var[1];
            if($var[0] == 'respCode' && $var[1] != '00'){
            	throw new \Exception($resultReson['errMsg'], 1);
            }
            if($var[0] == 'result_json'){
                $desMethod = new \App\Library\DES3(Pay::$encKey);

                $resultStr = $desMethod->decrypt($urldeocde ===true ? urldecode($var[1]):$var[1]);
                break;
            }
        }
        if($resultStr){
            $result_json = json_decode($resultStr,true);
           return $result_json;

        }
        return null;
    }

    public static function pushOrder($fee,$payType = 'jsPay',$orderID,$title,$orderDesc,$openid = null)
    {
        $params = array(
            "amount" => $fee,
            "payType" => $payType,
            "orderId" => Pay::getMillisecond(),
            "businessTime" => Pay::getMillisecond(),
            "notifyUrl" => getenv('ORDER_NOTIFY'),
            "orderDesc" => $orderDesc,
            "errCodeDes" => "支付成功",
            "merchantId" => Pay::$merChantId,
            "merchantName" => "点点付"
        );
        if(!empty($openid)){
        	$params['openid'] = $openid;
        }
        $result = null;
        Pay::request(Pay::$testURL, Pay::$partnerId, "pushOrder", $params, $result, 0);
        return Pay::parseResult($result);
    }

    public static function notifyMerchant()
    {
        $params = array(
            "amount" => 2,
            "orderNo" => "2016051500012415",
            "codeStr" => "weixin://wxpay/bizpayurl?pr=VzPgpdw",
            "tradeStatus" => 3,
            "errCode" => "00",
            "errCodeDes" => "支付成功",
            "orderId" => "20160515232303839945310001",
            "merchantId" => Pay::$merChantId,
            "merchantName" => "pay");
        $result = null;
        Pay::request("www.baidu.com", Pay::$partnerId, "notify", $params, $result, 1);
        echo "\n notifyMerchant end:" . $result;
    }

    public static function queryOrder()
    {
        $params = array(
            "orderId" => Pay::getMillisecond(),
            "orderNo" => Pay::getMillisecond(),
            "merchantId" => Pay::$merChantId);
        $result = null;
        Pay::request(Pay::$testURL, Pay::$partnerId, "queryOrder", $params, $result, 0);
        echo "\nqueryOrder end:" . $result;
    }

}
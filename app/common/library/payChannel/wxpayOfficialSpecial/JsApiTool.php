<?php

// 还原自 Swoole Compiler 加密文件: JsApiTool.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/wxpayOfficialSpecial/JsApiTool.php

namespace app\common\library\payChannel\wxpayOfficialSpecial;

class JsApiTool{
    private $appid;
    private $appsecret;
    public $data;
    const GET_AUTH_CODE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const GET_ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const GET_MINIAPP_TOKEN_URL = 'https://api.weixin.qq.com/sns/jscode2session';
    public function __construct($appid, $appsecret)
    {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
        return;
    }

    public function getopenid(): string
    {
        return $this->getopenidfrommp($_GET['code']);
    }

    public function getloginurl(string|null $order_no = null): string
    {
        if (function_exists('is_https')) {
            if (is_https()) {
                $url = 'https://';
            } else {
                $url = 'http://';
            }
            $url = $url . $_SERVER['HTTP_HOST'] . '/';
        } else {
            if ($_SERVER['SERVER_PORT'] == 443) {
                $url = 'https://';
            } else {
                $url = 'http://';
            }
            $url = $url . $_SERVER['HTTP_HOST'] . '/';
        }
        $params = [
            'appid' => $this->appid,
            'redirect_uri' => $url . 'payment/wxpay/jsapiPay/' . $order_no,
            'response_type' => 'code',
            'scope' => 'snsapi_base',
            'state' => 'STATE',
        ];
        return self::GET_AUTH_CODE_URL . '?' . http_build_query($params) . '#wechat_redirect';
    }

    public function getopenidfrommp(string|null $code = null): string
    {
        $params = [
            'appid' => $this->appid,
            'secret' => $this->appsecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $response = $this->curl(self::GET_ACCESS_TOKEN_URL . '?' . http_build_query($params));
        $data = json_decode($response, true);
        if (isset($data['access_token']) && isset($data['openid'])) {
            $this->data = $data;
            return $data['openid'];
        }
        if (isset($data['errcode'])) {
            throw new \ba\Exception('Openid获取失败 [' . $data['errcode'] . ']' . $data['errmsg']);
        }
        throw new \ba\Exception('Openid获取失败，原因未知');
    }

    public function appgetopenid(string|null $code = null): string
    {
        $params = [
            'appid' => $this->appid,
            'secret' => $this->appsecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $response = $this->curl(self::GET_MINIAPP_TOKEN_URL . '?' . http_build_query($params));
        $data = json_decode($response, true);
        if (isset($data['session_key']) && isset($data['openid'])) {
            $this->data = $data;
            return $data['openid'];
        }
        if (isset($data['errcode'])) {
            throw new \ba\Exception('获取openid失败 [' . $data['errcode'] . ']' . $data['errmsg']);
        }
        throw new \ba\Exception('获取openid失败，原因未知');
    }

    private function curl(string|null $url = null): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 4.0.4; es-mx; HTC_One_X Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0');
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}

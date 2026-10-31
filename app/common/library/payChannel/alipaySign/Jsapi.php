<?php

// 还原自 Swoole Compiler 加密文件: Jsapi.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/alipaySign/Jsapi.php

namespace app\common\library\payChannel\alipaySign;

class Jsapi extends \app\common\library\payChannel\AbstractAlipaySign {
    public function pay(\app\admin\model\Order|null $order = null): \app\common\service\channel\PayInfo
    {
        $appId = $this->entryChannel->config['appId'];
        $url = urlencode(request()->domain() . '/payment/alipay/openId/authPay/' . $order->order_no);
        $state = generate_random_string();
        $authUrl = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=' . $appId . '&scope=auth_user&redirect_uri=' . $url . '&state=' . $state;
        return new \app\common\service\channel\PayInfo(['qrcode' => $authUrl]);
    }

    public function getminiurl(\app\admin\model\Order|null $order = null): string
    {
        $appId = $this->getMiniAppId();
        $url = 'alipays://platformapi/startapp?appId=' . $appId . '&page=pages%2Fpay%2Fpay%3F';
        $arr = [];
        $arr['money'] = $order->pay_money;
        $arr['url'] = request()->domain() . '/payment/alipay/openId/miniPay/' . $order->order_no;
        $query = http_build_query($arr);
        return $url . urlencode($query);
    }

    public function getminiappid(): string
    {
        return $this->merchant->merchant_info['mini_app_id'] ?? '';
    }

    public function getpay(\app\admin\model\Order|null $order = null): array
    {
        $this->init($this->config);
        if (empty($this->merchant->merchant_info['app_auth_token'])) {
            throw new \ba\Exception('商户应用未完成授权');
        }
        $params = [
            'out_trade_no' => $order->order_no,
            'total_amount' => $order->pay_money,
            'subject' => $order->name,
            'product_code' => 'JSAPI_PAY',
            'op_app_id' => $this->merchant->merchant_info['mini_app_id'] ?? '',
        ];
        if (isset($order->extend['open_id'])) {
            $params['buyer_open_id'] = $order->extend['open_id'];
        }
        if (isset($order->extend['user_id'])) {
            $params['buyer_id'] = $order->extend['user_id'];
        }
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute($this->getapi(), ['app_auth_token' => $this->merchant->merchant_info['app_auth_token']], $params);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('支付发起失败：' . $result->subMsg);
        }
        $response = json_decode($result->httpBody, true)['alipay_trade_create_response'];
        if (empty($response['trade_no'])) {
            throw new \ba\Exception('交易平台订单号为空');
        }
        $order->trade_no = $response['trade_no'];
        $order->save();
        return $response;
    }

}

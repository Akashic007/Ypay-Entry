<?php

// 还原自 Swoole Compiler 加密文件: EpayV1.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/sys/payChannel/EpayV1.php

namespace app\common\library\sys\payChannel;

class EpayV1 implements \app\common\service\sys\payment\ChannelInterface {
    private string $pid;
    private string $key;
    private string $url;
    private string $sign_type = 'MD5';
    private string $method;
    private string $pay_type;
    public function __construct(\app\admin\model\sys\PayChannel $channel)
    {
        $config = $channel->config ?? [];
        if (empty($config['pid'])) {
            throw new \ba\Exception('商户ID为空');
        }
        if (empty($config['key'])) {
            throw new \ba\Exception('商户密钥为空');
        }
        if (empty($config['address'])) {
            throw new \ba\Exception('API地址为空');
        }
        $this->pid = $config['pid'];
        $this->key = $config['key'];
        $this->url = $config['address'];
        $this->method = $config['method'] ?? 'submit';
        $this->pay_type = $channel->pay_type;
    }


    public function pay(\app\admin\model\sys\Order|null $order = null): array
    {
        $params = [
            'pid'          => $this->pid,
            'type'         => $this->pay_type,
            'notify_url'   => request()->domain() . '/notify/sysPay/epayV1/notify',
            'return_url'   => request()->domain() . '/notify/sysPay/epayV1/returnUrl',
            'out_trade_no' => $order->order_no,
            'name'         => $order->desc, // [opcode 对照] lit '\UKW'(4B) dec='klso' 不可辨，按语义取 sys_order.desc（商品描述），见 B54 报告
            'money'        => $order->payment_amount,
        ];
        if ($this->method == 'submit') {
            $link = $this->getPayLink($params);
            return PayInfoRes::url($link);
        }
        $params['clientip'] = request()->ip();
        $res = $this->apiPay($params);
        if ($res['code'] != '1') {
            throw new \ba\Exception($res['msg']);
        }
        if (isset($res['qrcode'])) {
            return PayInfoRes::qrcode($res['qrcode']);
        }
        if (isset($res['payurl'])) {
            return PayInfoRes::url($res['payurl']);
        }
        return PayInfoRes::qrcode($res['urlscheme']);
    }


    public function callback(\app\admin\model\sys\Order|null $order = null, array|null $params = null): void
    {
        if (!$this->verify($params, $params['sign'])) {
            throw new \ba\Exception('签名验证失败');
        }
        if ($params['trade_status'] != 'TRADE_SUCCESS') {
            throw new \ba\Exception('订单支付失败');
        }
    }


    public function pagepay($param_tmp, $button = '正在跳转'): string
    {
        $param = $this->buildRequestParam($param_tmp);
        $html = '<form id="dopay" action="' . $this->url . 'submit.php" method="post">';
        foreach ($param as $k => $v) {
            $html .= '<input type="hidden" name="' . $k . '" value="' . $v . '"/>';
        }
        $html .= '<input type="submit" value="' . $button . '"></form><script>document.getElementById("dopay").submit();</script>';
        return $html;
    }


    public function getpaylink($param_tmp): string
    {
        $param = $this->buildRequestParam($param_tmp);
        return $this->url . 'submit.php?' . http_build_query($param);
    }


    public function apipay($param_tmp): array
    {
        $param = $this->buildRequestParam($param_tmp);
        $res = $this->getHttpResponse($this->url . 'mapi.php', http_build_query($param));
        if (!$res) {
            return ['code' => '0', '支付请求异常'];
        }
        return json_decode($res, true);
    }


    public function verify($param_tmp, $ver_sign): bool
    {
        $sign = $this->getSign($param_tmp);
        return $sign === $ver_sign;
    }


    public function verifynotify(): bool
    {
        if (empty($_GET)) {
            return false;
        }
        $sign = $this->getSign($_GET);
        return $sign === $_GET['sign'];
    }


    public function verifyreturn(): bool
    {
        if (empty($_GET)) {
            return false;
        }
        $sign = $this->getSign($_GET);
        return $sign === $_GET['sign'];
    }


    public function orderstatus($trade_no): bool
    {
        $res = $this->queryOrder($trade_no);
        if ($res['status'] == 1) {
            return true;
        }
        return false;
    }


    public function queryorder($trade_no)
    {
        $url = $this->url . '?act=order&pid=' . $this->pid . '&key=' . $this->key . '&trade_no=' . $trade_no;
        $res = $this->getHttpResponse($url);
        return json_decode($res, true);
    }


    public function refund($trade_no, $money)
    {
        $res = $this->getHttpResponse($this->url . '?act=refund', 'pid=' . $this->pid . '&key=' . $this->key . '&trade_no=' . $trade_no . '&money=' . $money);
        return json_decode($res, true);
    }


    private function buildrequestparam($param)
    {
        $sign = $this->getSign($param);
        $param['sign'] = $sign;
        $param['sign_type'] = $this->sign_type;
        return $param;
    }


    private function getsign($param): string
    {
        ksort($param);
        reset($param); // [opcode 对照] lit 'K\\KPT'(5B)=reset，epay 经典签名流程，行为无副作用
        $signStr = '';
        foreach ($param as $k => $v) {
            if ($k != 'sign' && $k != 'sign_type' && $v != '') {
                $signStr .= $k . '=' . $v . '&';
            }
        }
        $signStr = substr($signStr, 0, -1);
        $signStr .= $this->key;
        return md5($signStr);
    }


    private function gethttpresponse($url, $post = false, $timeout = 10): bool|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // [opcode 对照] HTTPHEADER 数组第 2 元素 zval 在 dump 层为 UNDEF(值指针丢失)，字面量池中仅有 lit25/26/27 三条 header，按序补 'Connection: close'
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: */*', 'Connection: close', 'Accept-Language: zh-CN,zh;q=0.8']);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


}

<?php

// 还原自 Swoole Compiler 加密文件: AlipayScan.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/alipay/AlipayScan.php

namespace app\common\library\payChannel\alipay;

class AlipayScan extends \app\common\library\payChannel\AbstractAlipay {
    public function pay(\app\admin\model\Order|null $order = null): \app\common\service\channel\PayInfo
    {
        $this->init($this->config);
        $params = [
            'out_trade_no' => $order->order_no,
            'total_amount' => (string) $order->pay_money,
            'subject' => $order->name,
            'product_code' => 'FACE_TO_FACE_PAYMENT',
        ];
        if (!empty($order->expire_time)) {
            $params['time_expire'] = date('Y-m-d H:i:s', $order->expire_time);
        }
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.trade.precreate', [], $params);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('支付发起失败：' . ($result->subMsg ?? $result->msg ?? '未知错误'));
        }
        $response = json_decode($result->httpBody, true);
        $response = $response['alipay_trade_precreate_response'] ?? [];
        if (empty($response['qr_code'])) {
            throw new \ba\Exception('支付宝扫码支付二维码为空');
        }
        return new \app\common\service\channel\PayInfo(['qrcode' => $response['qr_code']]);
    }

    public function callback(\app\admin\model\Order|null $order = null): bool
    {
        try {
            $this->init($this->config);
            $data = request()->post('', null, null);
            if (!\Alipay\EasySDK\Kernel\Factory::payment()->common()->verifyNotify($data)) {
                return false;
            }
            if (!empty($data['trade_no'])) {
                $order->trade_no = $data['trade_no'];
                $order->save();
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // [opcode 对照] $scene 默认值按 RECV_INIT op2=LIT('add') 真值补回（原签名 null 为管线丢失）
    public function handleparams(array|null $params = null, string|null $scene = 'add'): void
    {
        $config = ['mode' => 'key'];
        if ($scene == 'add') {
            $config['mode'] = $this->account->config['mode'] ?? 'key';
        }
        if (isset($params['mode']) && $params['mode'] == 'cert') {
            $config['mode'] = 'cert';
        }
        if (empty($this->account->config['appId'])) {
            throw new \ba\Exception('支付宝应用ID为空');
        }
        if (empty($this->account->config['appPrivateKey'])) {
            throw new \ba\Exception('支付宝应用私钥为空');
        }
        if ($config['mode'] == 'key') {
            if (empty($this->account->config['alipayPublicKey'])) {
                throw new \ba\Exception('支付宝公钥为空');
            }
        }
        if ($config['mode'] == 'cert') {
            if (empty($this->account->config['appCertPem']) || empty($this->account->config['alipayPublicCertPem']) || empty($this->account->config['alipayRootCert'])) {
                throw new \ba\Exception('缺乏必要证书文件');
            }
        }
    }

    public function handledelete(): void
    {
    return;
    }

    public function refund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
        $this->init($this->config);
        $params = [
            'refund_amount' => (string) $record->refund_amount,
            'refund_reason' => $record->refund_reason ?? '',
            'out_request_no' => $record->refund_no,
            'query_options' => ['refund_detail_item_list'],
        ];
        if (empty($order->trade_no)) {
            $params['out_trade_no'] = $order->order_no;
        } else {
            $params['trade_no'] = $order->trade_no;
        }
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.trade.refund', [], $params);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('退款失败：' . ($result->subMsg ?? $result->msg ?? '未知错误'));
        }
        $response = json_decode($result->httpBody, true);
        $response = $response['alipay_trade_refund_response'] ?? [];
        $this->saveRefundResult($record, $response, ($response['fund_change'] ?? '') === 'Y');
    }

    public function queryrefund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
        $this->init($this->config);
        $params = [
            'out_request_no' => $record->refund_no,
            'query_options' => ['refund_detail_item_list', 'gmt_refund_pay'],
        ];
        if (empty($order->trade_no)) {
            $params['out_trade_no'] = $order->order_no;
        } else {
            $params['trade_no'] = $order->trade_no;
        }
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.trade.fastpay.refund.query', [], $params);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('退款查询失败：' . ($result->subMsg ?? $result->msg ?? '未知错误'));
        }
        $response = json_decode($result->httpBody, true);
        $response = $response['alipay_trade_fastpay_refund_query_response'] ?? [];
        $this->saveRefundResult($record, $response, ($response['refund_status'] ?? '') == 'REFUND_SUCCESS');
    }

    public function query(\app\admin\model\Order|null $order = null): array
    {
        $this->init($this->config);
        $params = [];
        if (empty($order->trade_no)) {
            $params['out_trade_no'] = $order->order_no;
        } else {
            $params['trade_no'] = $order->trade_no;
        }
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.trade.query', [], $params);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('订单查询失败：' . ($result->subMsg ?? $result->msg ?? '未知错误'));
        }
        return json_decode($result->httpBody, true)['alipay_trade_query_response'] ?? [];
    }

    protected function getnotifyurl(): string
    {
        return request()->domain() . '/notify/pay/alipayScan/payCallback';
    }

    private function saverefundresult(\app\admin\model\RefundRecord|null $record = null, array|null $data = null, bool|null $success = null): void
    {
        if (!$success) {
            if (($data['fund_change'] ?? '') !== 'Y') {
                return;
            }
        }
        $record->status = 1;
        $record->refund_channel = $this->getRefundChannel($data);
        $record->refund_trade_no = $data['trade_no'] ?? '';
        $record->refund_account = $data['buyer_logon_id'] ?? ($data['buyer_user_id'] ?? ($data['buyer_open_id'] ?? '原路退款'));
        if (!empty($data['refund_fee'])) {
            $record->refund_amount = $data['refund_fee'];
        } elseif (!empty($data['refund_amount'])) {
            $record->refund_amount = $data['refund_amount'];
        }
        $record->refund_time = time();
        $record->save();
    }

    private function getrefundchannel(array|null $data = null): string
    {
        $channel = $data['refund_detail_item_list']['fund_channel'] ?? null;
        if (is_array($channel)) {
            return $channel[0]['fund_channel'] ?? ($channel['fund_channel'] ?? 'ALIPAY');
        }
        return $channel ?: 'ALIPAY';
    }

}

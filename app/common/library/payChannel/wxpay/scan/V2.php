<?php

// 还原自 Swoole Compiler 加密文件: V2.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/wxpay/scan/V2.php

namespace app\common\library\payChannel\wxpay\scan;

class V2 extends \app\common\library\payChannel\WxpayScan {
    public function pay(\app\admin\model\Order|null $order = null): \app\common\service\channel\PayInfo
    {
        $pay = \WeChat\Pay::instance($this->initConfig());
        $params = [
            'body' => $order->name,
            'out_trade_no' => $order->order_no,
            'total_fee' => yuantofen($order->pay_money),
            'trade_type' => 'NATIVE',
            'notify_url' => request()->domain() . '/notify/pay/wxpayScan/payCallback',
            'spbill_create_ip' => request()->ip(),
        ];
        try {
            $response = $pay->createOrder($params);
        } catch (Exception $e) {
            throw new \ba\Exception($e->getMessage());
        }
        if ($response['return_code'] == 'FAIL') {
            throw new \ba\Exception($response['err_code'] . '：' . $response['err_code_des']);
        }
        return new \app\common\service\channel\PayInfo(['qrcode' => $response['code_url']]);
    }

    public function callback(\app\admin\model\Order|null $order = null): bool
    {
        try {
            $pay = \WeChat\Pay::instance($this->initConfig());
            $response = $pay->getNotify();
        } catch (Throwable $e) {
            return false;
        }
        if ($response['return_code'] === 'SUCCESS') {
            if ($response['result_code'] === 'SUCCESS') {
                return true;
            }
        }
        return false;
    }

    public function handleparams(array|null $params = null, string|null $scene = 'add'): void
    {
        $config = $this->account->config;
        if (empty($params['mch_id'])) {
            throw new \ba\Exception('商户ID为空');
        }
        if (empty($config['app_id'])) {
            throw new \ba\Exception('应用ID为空');
        }
        if (empty($config['mch_key'])) {
            throw new \ba\Exception('商户密钥为空');
        }
        $config['api_version'] = 'v2';
        $this->account->config = $config;
        $this->account->save();
    }

    public function handledelete(): void
    {
    return;
    }

    public function refund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
        throw new \ba\Exception('退款功能暂不支持');
    }

    public function queryrefund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
        throw new \ba\Exception('退款功能暂不支持');
    }

    private function initconfig(): array
    {
        if (empty($this->account->config['app_id'])) {
            throw new \ba\Exception('应用ID为空');
        }
        if (empty($this->account->mch_id)) {
            throw new \ba\Exception('商户ID为空');
        }
        if (empty($this->account->config['mch_key'])) {
            throw new \ba\Exception('商户密钥为空');
        }
        return [
            'appid' => $this->account->config['app_id'],
            'mch_id' => $this->account->mch_id,
            'mch_key' => $this->account->config['mch_key'],
        ];
    }

}

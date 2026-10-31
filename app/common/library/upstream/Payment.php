<?php

// 还原自 Swoole Compiler 加密文件: Payment.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/upstream/Payment.php

namespace app\common\library\upstream;

class Payment{
    protected \app\admin\model\supply\Upstream $upstream;
    public function __construct(\app\admin\model\supply\Upstream $upstream)
    {
        $this->upstream = $upstream;
    }


    public function pay(\app\admin\model\Order|null $order = null): array
    {
        if ($order->channel->source_type != 'up') {
            throw new \Exception('支付通道非上游通道');
        }
        if ($order->channel->source_id != $this->upstream->id) {
            throw new \Exception('支付通道与上游信息不一致');
        }
        $params = [
            'type'        => $order->type,
            'out_trade_no' => $order->order_no,
            'name'        => $order->name,
            'money'       => $order->pay_money,
            'channel_id'  => $order->channel->source_cid,
            'client_ip'   => request()->ip(),
        ];
        $ur = new UpRequest($this->upstream);
        $ur->setUrl('/supply/Payment/pay');
        $ur->setParams($params);
        $res = $ur->httpUrl();
        if ($res['code'] != 1) {
            throw new \Exception($res['msg']);
        }
        if (empty($res['data'])) {
            throw new \Exception('申请响应结果为空');
        }
        if (empty($res['data']['trade_no'])) {
            throw new \Exception('上游订单号为空');
        }
        if (empty($res['data']['pay_info'])) {
            throw new \Exception('上游支付结果为空');
        }
        return $res['data'];
    }


    public function queryorder(string|null $order_no = null): array
    {
        $ur = new UpRequest($this->upstream);
        $ur->setUrl('/supply/Payment/queryOrder');
        $ur->setParams(['order_no' => $order_no]);
        $res = $ur->httpUrl('GET');
        if ($res['code'] != 1) {
            throw new \Exception($res['msg']);
        }
        if (empty($res['data'])) {
            throw new \Exception('申请响应结果为空');
        }
        if (empty($res['data']['order_no'])) {
            throw new \Exception('上游订单号为空');
        }
        if (empty($res['data']['status'])) {
            throw new \Exception('上游订单状态为空');
        }
        if (empty($res['data']['sign'])) {
            throw new \Exception('签名为空');
        }
        return $res['data'];
    }


}

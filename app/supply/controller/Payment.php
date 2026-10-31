<?php

// 还原自 Swoole Compiler 加密文件: Payment.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/supply/controller/Payment.php

namespace app\supply\controller;

class Payment extends \app\common\controller\SupplyBase {
    public function pay(): void
    {
        if (!request()->isPost()) {
            $this->error('请求方式错误');
        }
        $params = request()->post();
        $user = $this->user;
        try {
            $params['source'] = 'down';
            $params['pid'] = $user->id;
            $params['notify_url'] = $this->downstream->domain . '/notify/pay/upstream/callback/' . $params['out_trade_no'];
            $params['return_url'] = $this->downstream->domain . '/notify/pay/upstream/callback/' . $params['out_trade_no'];
            $this->checkParams($params);
            $orderService = new \app\common\service\OrderService();
            $orderService->validateData($params);
            $params['money'] = floatval($params['money']);
            $order = $orderService->insertOrder($params, $user);
            $payService = new \app\common\service\PayService();
            $payService->pay($order);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        if (empty($order->pay_info['qrcode'])) {
            $this->error('支付二维码获取失败');
        }
        $this->success('ok', [
            'trade_no'  => $order->order_no,
            'name'      => $order->name,
            'type'      => $order->type,
            'pay_money' => $order->pay_money,
            'pay_info'  => $order->pay_info,
        ]);
    }

    public function queryorder(): void
    {
        $orderNo = request()->param('order_no');
        if (empty($orderNo)) {
            $this->error('订单号为空');
        }
        $order = \app\admin\model\Order::hasWhere('channel', function ($query) {
            $query->where('source_type', 'down')->where('source_id', $this->downstream->id);
        })->where('out_trade_no', $orderNo)->where('source', 'down')->find();
        if (empty($order)) {
            $this->error('订单不存在');
        }
        $order = $order->toArray();
        $data = [
            'order_no'      => $order['order_no'],
            'status'        => $order['status'],
            'notify_status' => $order['notify_status'],
            'name'          => $order['name'],
            'pay_money'     => $order['pay_money'],
            'money'         => $order['money'],
        ];
        $data['sign'] = \app\common\library\SignUtil::generateSign($data, $this->agent->api_key);
        $this->success('ok', $data);
    }

    public function refund(): void
    {
        $params = request()->post(['order_no', 'amount', 'reason']);
        if (empty($params['order_no'])) {
            $this->error('订单号为空');
        }
        if (empty($params['amount'])) {
            $this->error('退款金额为空');
        }
        $order = \app\admin\model\Order::where('out_trade_no', $params['order_no'])->where('source', 'down')->find();
        if (empty($order)) {
            $this->error('订单不存在');
        }
        if ($order->channel->source_type !== 'down') {
            $this->error('订单来源错误');
        }
        if ($order->channel->source_id != $this->downstream->id) {
            $this->error('订单权限不足');
        }
        try {
            $orderService = new \app\common\service\OrderService();
            $refund = $orderService->createRefund($order, $params['amount'], ['reason' => $params['reason']]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('申请成功', [
            'trade_no'       => $order->out_trade_no,
            'refund_no'      => $refund->refund_no,
            'refund_channel' => $refund->refund_channel,
            'refund_account' => $refund->refund_account,
            'status'         => $refund->status,
            'order_amount'   => $refund->order_amount,
            'refund_amount'  => $refund->refund_amount,
        ]);
    }

    public function queryrefund(): void
    {
        $params = request()->param(['order_no', 'refund_no']);
        if (empty($params['order_no'])) {
            $this->error('订单号为空');
        }
        if (empty($params['refund_no'])) {
            $this->error('退款单号为空');
        }
        $order = \app\admin\model\Order::where('out_trade_no', $params['order_no'])->where('source', 'down')->find();
        if (empty($order)) {
            $this->error('订单不存在');
        }
        if ($order->channel->source_type !== 'down') {
            $this->error('订单来源错误');
        }
        if ($order->channel->source_id != $this->downstream->id) {
            $this->error('订单权限不足');
        }
        $record = \app\admin\model\RefundRecord::where('order_no', $order->order_no)->where(function ($query) use ($params) {
            $query->where('refund_no', $params['refund_no'])->whereOr('refund_trade_no', $params['refund_no']);
        })->find();
        if (empty($record)) {
            $this->error('退款记录不存在');
        }
        try {
            $payService = new \app\common\service\PayService();
            $payService->queryRefund($order, $record);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('ok', [
            'trade_no'        => $order->out_trade_no,
            'refund_no'       => $record->refund_no,
            'refund_trade_no' => $record->refund_trade_no,
            'refund_channel'  => $record->refund_channel,
            'refund_account'  => $record->refund_account,
            'status'          => $record->status,
            'order_amount'    => $record->order_amount,
            'refund_amount'   => $record->refund_amount,
        ]);
    }

    public function getpayinfo(): void
    {
        return;
    }

    private function checkparams(array|null $params = null): void
    {
        if (empty($params['type'])) {
            throw new \ba\Exception('支付方式为空');
        }
        if (empty($params['out_trade_no'])) {
            throw new \ba\Exception('下游订单号为空');
        }
        if (empty($params['name'])) {
            throw new \ba\Exception('商品名称为空');
        }
        if (empty($params['money'])) {
            throw new \ba\Exception('订单金额为空');
        }
        if (empty($params['channel_id'])) {
            throw new \ba\Exception('渠道ID为空');
        }
        return;
    }

}

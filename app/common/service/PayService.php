<?php

// 还原自 Swoole Compiler 加密文件: PayService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/PayService.php
//
// [2026-09-22 opcode 全量对照还原] 依据 /tools dump_m.py 逐指令重建：
//   - pay: validateOrder 门禁 → channel_id 为空走通道池循环(resolveAccountsForPay + channelDispatcher
//     轮询，单通道失败换下一个，全部失败抛 通道循环支付异常：+最后错误)；已指定 channel_id 则直接
//     找账号初始化通道支付(失败抛 通道初始化错误：)。
//   - floatingAmountChannel 内的通道支付前调 setFloatingAmount 给 pay_money 加随机尾数直到唯一。

namespace app\common\service;

class PayService{
    private array $floatingAmountChannel = ['alipay_personal_not_hang', 'wxpay_software'];
    public function pay(\app\admin\model\Order|null $order = null): array
    {
        try {
            $this->validateOrder($order);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        if (empty($order->channel_id)) {
            // [opcode 对照] 条件指令 ISSET_ISEMPTY_PROP_OBJ 无法区分 isset/empty 两种编译产物；
            // channel_id 为 NULL 或有效账号 id 时两种写法行为一致，取 empty（0 值回退通道池更安全）
            $channelPoolService = new ChannelPoolService();
            $accounts = $channelPoolService->resolveAccountsForPay($order);
            $list = $accounts['accounts'];
            if (count($list) <= 0) {
                throw new \Exception('暂无可用通道');
            }
            $channelService = new ChannelService();
            $channels = $channelService->channelDispatcher($list, $accounts['mode']);
            $payInfo = '未知';
            foreach ($channels as $item) {
                try {
                    $account = \app\admin\model\channel\Account::find($item['id']);
                    $channel = \app\common\service\channel\ChannelFactory::create($account);
                    if (in_array($account->channel_type, $this->floatingAmountChannel)) {
                        $this->setFloatingAmount($order, $account);
                    }
                    $payResult = $channel->pay($order);
                    $order->channel_id = $account->id;
                    $order->pay_info = $payResult->getPayInfo();
                    $order->save();
                    return $payResult->getPayInfo();
                } catch (\Exception $e) {
                    $payInfo = $e->getMessage();
                }
            }
            throw new \Exception('通道循环支付异常：' . $payInfo);
        }

        try {
            $account = \app\admin\model\channel\Account::find($order->channel_id);
            $channel = \app\common\service\channel\ChannelFactory::create($account);
            if (in_array($account->channel_type, $this->floatingAmountChannel)) {
                $this->setFloatingAmount($order, $account);
            }
            $payResult = $channel->pay($order);
            $order->pay_info = $payResult->getPayInfo();
            $order->save();
        } catch (\Exception $e) {
            throw new \Exception('通道初始化错误：' . $e->getMessage());
        }
        return $payResult->getPayInfo();
    }

    public function refund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
        try {
            $channel = \app\common\service\channel\ChannelFactory::create($order->channel);
            if (!$channel instanceof \app\common\service\channel\Refundable) {
                throw new \ba\Exception('该通道不支持退款功能');
            }
            $channel->refund($order, $record);
        } catch (\Throwable $e) {
            throw new \ba\Exception($e->getMessage());
        }
    }

    public function queryrefund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
        try {
            $channel = \app\common\service\channel\ChannelFactory::create($order->channel);
            if (!$channel instanceof \app\common\service\channel\Refundable) {
                throw new \ba\Exception('该通道不支持退款查询');
            }
            $channel->queryRefund($order, $record);
        } catch (\Throwable $e) {
            throw new \ba\Exception($e->getMessage());
        }
    }

    private function validateorder(\app\admin\model\Order|null $order = null): void
    {
        if ($order->status != \app\admin\model\Order::STATUS_WAIT) {
            throw new \Exception('订单' . $order->status_text);
        }
        if ($order->expire_time < time()) {
            throw new \Exception('订单已过期');
        }
    }

    private function setfloatingamount(\app\admin\model\Order|null $order = null, \app\admin\model\channel\Account|null $account = null): void
    {
        // 浮动金额候选：用户配置 floating_amount 逗号分隔，缺省 0.01~0.40；
        // 过滤保留数值且 (0, 1.0] 的项；全空回退 0.01~0.05
        $amounts = empty($order->userConfig->channel_config['floating_amount'])
            ? ['0.01', '0.02', '0.03', '0.04', '0.05', '0.06', '0.07', '0.08', '0.09', '0.10', '0.11', '0.12', '0.13', '0.14', '0.15', '0.16', '0.17', '0.18', '0.19', '0.20', '0.21', '0.22', '0.23', '0.24', '0.25', '0.26', '0.27', '0.28', '0.29', '0.30', '0.31', '0.32', '0.33', '0.34', '0.35', '0.36', '0.37', '0.38', '0.39', '0.40']
            : explode(',', $order->userConfig->channel_config['floating_amount']);
        $amounts = array_filter($amounts, function ($amount) {
            return is_numeric(trim($amount)) && 0 < floatval($amount) && floatval($amount) <= 1.0;
        });
        if (empty($amounts)) {
            $amounts = ['0.01', '0.02', '0.03', '0.04', '0.05'];
        }
        // 随机加尾数直到同通道待支付订单中金额唯一（便于对账），每次 +0.01~0.40 元
        while (true) {
            $lastOrderId = \app\admin\model\Order::where('status', 0)
                ->where('channel_id', $account->id)
                ->where('expire_time', '>', time())
                ->where('pay_money', bcmul($order->pay_money, 100))
                ->where('id', '<>', $order->id)
                ->order('id desc')
                ->value('id');
            if (empty($lastOrderId)) {
                break;
            }
            $floatingAmount = $amounts[array_rand($amounts)];
            $newPayMoney = bcadd((string) $order->pay_money, $floatingAmount, 2);
            $order->pay_money = $newPayMoney;
            $order->save();
        }
    }

}

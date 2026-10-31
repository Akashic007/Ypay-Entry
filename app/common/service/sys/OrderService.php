<?php

// 还原自 Swoole Compiler 加密文件: OrderService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/sys/OrderService.php

namespace app\common\service\sys;

class OrderService
{
    public function create(array|null $params = null, \app\common\model\User|null $user = null): \app\admin\model\sys\Order
    {
        try {
            \think\facade\Db::startTrans();
            $this->paramsval($params);
            $amount = $this->getamount($params);
            $order  = \app\admin\model\sys\Order::create([
                'user_id'         => $user->id,
                'order_no'        => $this->generateorderno(),
                'type'            => $params['type'],
                'order_amount'    => $amount['order_amount'] ?? 0,
                'discount_amount' => $amount['discount_amount'] ?? 0,
                'payment_amount'  => $amount['payment_amount'] ?? 0,
                'content'         => $this->getcontent($params, $user),
                'desc'            => $this->getdesc($params, $user),
                'result'          => '订单创建',
                'status'          => 0,
            ]);
            $this->pay($order, $params['pay_method']);
            \think\facade\Db::commit();
        } catch (\Exception $e) {
            \think\facade\Db::rollback();
            throw new \Exception($e->getMessage());
        }
        return $order;
    }

    public function pay(\app\admin\model\sys\Order|null $order = null, string|null $method = null): array
    {
        if ($order->status != 0) {
            if ($order->status == 1) {
                throw new \Exception('订单已支付');
            }
            if ($order->status == 2) {
                throw new \Exception('订单已取消');
            }
        }
        $payAmount = (float)$order->payment_amount;
        if ($payAmount <= 0) {
            throw new \Exception('订单支付金额小于0');
        }
        $channels = \app\admin\model\sys\PayChannel::where(['status' => 1, 'pay_type' => $method])
            ->order('weigh', 'desc')
            ->select();
        if (count($channels) <= 0) {
            throw new \Exception('暂无可用的支付通道');
        }
        $messages = '';
        foreach ($channels as $channel) {
            try {
                $channelService = \app\common\service\sys\payment\ChannelFactory::create($channel);
                $payInfo = $channelService->pay($order);
                $order->pay_info   = $payInfo;
                $order->channel_id = $channel->id;
                $order->save();
                return $payInfo;
            } catch (\Exception $e) {
                $messages .= $e->getMessage();
            }
        }
        throw new \Exception('所有通道调用失败:' . $messages);
    }

    public function callback(\app\admin\model\sys\Order|null $order = null, array|null $params = null): void
    {
        if (empty($params)) {
            throw new \Exception('回调参数为空');
        }
        $channel = \app\admin\model\sys\PayChannel::find($order->channel_id);
        if (empty($channel)) {
            throw new \Exception('支付通道不存在');
        }
        $service = \app\common\service\sys\payment\ChannelFactory::create($channel);
        try {
            $service->callback($order, $params);
        } catch (\Exception $e) {
            $order->result = $e->getMessage();
            $order->save();
            throw new \Exception($e->getMessage());
        }
        if ($order->status != 1) {
            $this->callbacksuccess($order);
        }
    }

    public function callbacksuccess(\app\admin\model\sys\Order|null $order = null): void
    {
        $order->result   = '订单回调验证成功';
        $order->status   = 1;
        $order->pay_time = time();
        $order->save();
        switch ($order->type) {
            case 1:
                \think\facade\Db::startTrans();
                try {
                    \app\admin\model\UserMoneyLog::create([
                        'user_id' => $order->user_id,
                        'money'   => $order->payment_amount,
                        'memo'    => '用户充值' . $order->payment_amount . '元',
                        'type'    => \app\admin\model\UserMoneyLog::TYPE_RECHARGE,
                    ]);
                    $user       = \app\admin\model\User::find($order->user_id);
                    $siteProfit = new \app\common\service\SiteProfitService();
                    $siteProfit->handleRecharge($user, (string)$order->payment_amount);
                    $order->result = '余额充值成功';
                    $order->save();
                    \think\facade\Db::commit();
                } catch (\Throwable $e) {
                    \think\facade\Db::rollback();
                    $order->result   = '余额充值失败：' . $e->getMessage();
                    $order->status   = 0;
                    $order->pay_time = null;
                    $order->save();
                    throw new \Exception($e->getMessage());
                }
                break;
            default:
                $order->result = '订单暂无处理';
                $order->save();
                break;
        }
    }

    // [opcode 还原] prefix + date('YmdHis') + str_pad(rand(0,99999),5,'0',LEFT) 循环查重，重试间隔 usleep
    public static function generateorderno(string|null $prefix = null, int|null $maxTry = null): null|string
    {
        $prefix ??= 'YE';
        $maxTry ??= 10;
        for ($i = 0; $i < $maxTry; $i++) {
            $orderNo = $prefix . date('YmdHis') . str_pad((string) rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $exists  = \app\admin\model\sys\Order::where('order_no', $orderNo)->value('id');
            if (!$exists) {
                return $orderNo;
            }
            usleep(10000);
        }
        return null;
    }

    private function paramsval($params): void
    {
        if (empty($params['pay_method'])) {
            throw new \Exception('支付方式为空');
        }
        switch ($params['type']) {
            case '1':
                if (!isset($params['amount']) || $params['amount'] === '') {
                    throw new \Exception('充值金额不能为空');
                }
                if (!is_numeric($params['amount'])) {
                    throw new \Exception('充值金额必须是数字');
                }
                if ((float)$params['amount'] <= 0) {
                    throw new \Exception('充值金额必须大于0');
                }
                if ((float)$params['amount'] != round((float)$params['amount'], 2)) {
                    throw new \Exception('充值金额最多支持两位小数');
                }
                break;
            default:
                throw new \Exception('订单类型错误：' . $params['type']);
        }
    }

    private function getamount(array|null $params = null): array
    {
        switch ($params['type']) {
            case '1':
                return [
                    'order_amount'    => $params['amount'],
                    'discount_amount' => 0,
                    'payment_amount'  => $params['amount'],
                ];
            default:
                throw new \Exception('订单类型错误：' . $params['type']);
        }
    }

    private function getcontent(array|null $params = null, \app\common\model\User|null $user = null): array
    {
        switch ($params['type']) {
            case '1':
        }
        return [];
    }

    private static function getdesc(array|null $params = null, \app\common\model\User|null $user = null): string
    {
        switch ($params['type']) {
            case '1':
                $desc = '用户[' . $user->username . '] 充值余额：' . $params['amount'];
                return $desc;
            default:
                $desc = '常规订单';
                break;
        }
        return $desc;
    }
}

<?php

// 还原自 Swoole Compiler 加密文件: OrderService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/OrderService.php
//
// [2026-09-22 opcode 全量对照还原] 依据 /tools dump_m.py 逐指令重建（generateorderno 除外，按批次指示保持原样）。

namespace app\common\service;

class OrderService{
    public function createorder(array|null $params = null): \app\admin\model\Order
    {
        $user = \app\admin\model\User::find($params['pid']);
        \think\facade\Db::startTrans();
        try {
            $this->validateData($params);
            $this->checkSign($params, $user);
            $params['money'] = floatval($params['money']);
            $order = $this->insertOrder($params, $user);
            \think\facade\Event::trigger(new \app\common\event\OrderNotify($order, \app\common\event\OrderNotify::CREATE));
            \think\facade\Db::commit();
        } catch (\Exception $e) {
            \think\facade\Db::rollback();
            throw new \Exception($e->getMessage());
        }
        return $order;
    }
    public function createrefund(\app\admin\model\Order|null $order = null, float|null $amount = null, array|null $params = null): \app\admin\model\RefundRecord
    {
        if ($order->status != 1 && $order->status != 2) {
            throw new \ba\Exception('未支付订单不支持退款');
        }
        if ($order->pay_money <= 0) {
            throw new \Exception('订单金额小于0');
        }
        if ($amount <= 0) {
            throw new \ba\Exception('退款金额不能小于0');
        }
        if ($order->pay_money < $amount) {
            throw new \ba\Exception('退款金额不能大于订单金额');
        }
        $refundNo = self::generateOrderNo();
        $query = \app\admin\model\RefundRecord::where(['order_id' => $order->id, 'order_no' => $order->order_no])->whereIn('status', [0]);
        if ($query->count() > 0) {
            if (yuanToFen($order->pay_money) < $query->sum('refund_amount') + yuanToFen($amount)) {
                throw new \ba\Exception('累计退款金额超过订单金额');
            }
        }
        if (time() < $query->max('create_time') + 60) {
            throw new \ba\Exception('1分钟内只能发起一次退款');
        }
        \think\facade\Db::startTrans();
        try {
            $order->status = 2;
            $order->save();
            $record = \app\admin\model\RefundRecord::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'channel_id' => $order->channel_id,
                'order_no' => $order->order_no,
                'refund_no' => $refundNo,
                'order_amount' => $order->pay_money,
                'refund_amount' => $amount,
                'refund_reason' => $params['refund_reason'] ?? '',
                'status' => 0,
            ]);
            $payService = new PayService();
            $payService->refund($order, $record);
            \think\facade\Db::commit();
        } catch (\Exception $e) {
            \think\facade\Db::rollback();
            throw new \ba\Exception($e->getMessage());
        }
        return $record;
    }
    public function queryrefundrecode(\app\admin\model\Order|null $order = null): array
    {
        $query = \app\admin\model\RefundRecord::where('order_no', $order->order_no)->whereIn('status', [0]);
        return [
            'refund_num' => $query->count(),
            'refund_amount' => fenToYuan($query->sum('refund_amount')),
            'refundable_amount' => fenToYuan(yuanToFen($order->pay_money) - (int) $query->sum('refund_amount')),
        ];
    }
    public function insertorder(array|null $params = null, \app\admin\model\User|null $user = null): \app\admin\model\Order
    {
        $data = $this->buildOrderData($params, $user);
        return \app\admin\model\Order::create($data);
    }
    public function callback(\app\admin\model\Order|null $order = null): void
    {
        if ($order->status != 1) {
            $orderNotify = new \app\common\event\OrderNotify($order, \app\common\event\OrderNotify::PAY_SUCCESS, ['pay_time' => date('Y-m-d H:i:s')]);
            \think\facade\Event::trigger($orderNotify);
        }
        $order->status = 1;
        $order->pay_time = time();
        $order->notify_time = time();
        $order->save();
        $this->notifyCallback($order);
    }
    public function notifycallback(\app\admin\model\Order|null $order = null): void
    {
        try {
            \think\facade\Queue::push('app\job\OrderNotifyJob', ['order_id' => (int) $order->id]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    public function getnotifyurl(\app\admin\model\Order|null $order = null): array
    {
        try {
            if (!$this->deductFee($order)) {
                throw new \Exception('扣除手续费失败');
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $user = \app\admin\model\User::field(['id'])->find($order->user_id);
        $params = [
            'pid' => $order->user_id,
            'trade_no' => $order->order_no,
            'out_trade_no' => $order->out_trade_no,
            'name' => $order->name,
            'type' => $order->type,
            'money' => $order->money,
            'trade_status' => 'TRADE_SUCCESS',
            'param' => $order->extend_param,
        ];
        $userConfig = $order->userConfig;
        // 商户开启了 is_site_name_params 且订单带站点名时，把站点名并入签名参数
        if (!empty($order->site_name) && !empty($userConfig->channel_config['is_site_name_params']) && $userConfig->channel_config['is_site_name_params'] == 'open') {
            $params['sitename'] = $order->site_name;
        }
        $params['sign'] = \app\common\library\SignUtil::generateSign($params, $user->key, $order->sign_type ?? 'md5');
        $params['sign_type'] = $order->sign_type;

        $queryString = http_build_query($params);
        $result = [];
        if (!empty($order->return_url)) {
            if (strpos($order->return_url, '?')) {
                $result['return'] = $order->return_url . '&' . $queryString;
            } else {
                $result['return'] = $order->return_url . '?' . $queryString;
            }
        } else {
            $result['return'] = '';
        }
        if (strpos($order->notify_url, '?')) {
            $result['notify'] = $order->notify_url . '&' . $queryString;
        } else {
            $result['notify'] = $order->notify_url . '?' . $queryString;
        }
        $result['post_data'] = $params;
        return $result;
    }
    public function buildorderdata(array|null $params = null, \app\admin\model\User|null $user = null): array
    {
        $channelConfig = $user->userConfig->channel_config ?? [];
        return [
            'order_no' => $this->generateOrderNo($channelConfig['order_prefix'] ?? 'Y'),
            'user_id' => $user->id,
            'out_trade_no' => $params['out_trade_no'],
            'name' => $this->filterOrderName($params['name']),
            'type' => $params['type'],
            'channel_id' => $params['channel_id'] ?? '',
            'notify_url' => $params['notify_url'] ?? '',
            'return_url' => $params['return_url'] ?? '',
            'money' => (float) $params['money'],
            'pay_money' => (float) $params['money'],
            'status' => 0,
            'notify_status' => 0,
            'client_ip' => $params['clientip'] ?? $params['client_ip'] ?? '',
            'site_name' => $params['sitename'] ?? '',
            'device' => $params['device'] ?? '',
            'extend_param' => $params['param'] ?? '',
            'sign_type' => $params['sign_type'] ?? 'MD5',
            'expire_time' => time() + (int) ($channelConfig['timeout'] ?? 300),
            'is_test' => empty($params['is_test']) ? 0 : 1,
            'source' => $params['source'] ?? 'local',
        ];
    }
    public function validatedata(array|null $params = null): void
    {
        if (empty($params['pid'])) {
            throw new \Exception('商户ID为空');
        }
        if (empty($params['type'])) {
            throw new \Exception('支付方式为空');
        }
        if (empty($params['out_trade_no'])) {
            throw new \Exception('订单号为空');
        }
        if (empty($params['notify_url'])) {
            throw new \Exception('异步通知地址为空');
        }
        if (empty($params['name'])) {
            throw new \Exception('商品名称为空');
        }
        if (empty($params['money'])) {
            throw new \Exception('订单金额为空');
        }
        if (empty($params['sign'])) {
            throw new \Exception('签名不能为空');
        }
        if ($params['money'] < 0.01) {
            throw new \Exception('金额错误');
        }
        $user = \app\admin\model\User::with(['subscribe'])->find($params['pid']);
        if (empty($user)) {
            throw new \Exception('商户不存在');
        }
        if ($user->status != 'enable') {
            throw new \Exception('商户状态异常');
        }
        if (empty($params['source']) || $params['source'] != 'down') {
            if (empty($user->subscribe_id) || $user->subscribe_id <= 0) {
                throw new \Exception('商户暂未订阅套餐');
            }
            if (empty($user->sub_expire_time) || $user->sub_expire_time < time()) {
                throw new \Exception('商户订阅已到期');
            }
        }
        if (!empty($params['channel_id'])) {
            $account = \app\admin\model\channel\Account::find($params['channel_id']);
            if (empty($account)) {
                throw new \Exception('收款通道不存在');
            }
            if ($account->user_id != $user->id) {
                throw new \Exception('收款通道不属于此商户');
            }
            if ($account->status != 1) {
                throw new \Exception('通道收款状态未开启');
            }
            if ($account->online_status != 1) {
                throw new \Exception('收款通道处于离线状态');
            }
        }
        $orderRate = $user->subscribe->content['order_rate'] ?? 0;
        if (0 < $orderRate) {
            $orderRate = floatval($orderRate) / 100;
            $orderQuery = \app\admin\model\Order::where('user_id', $user->id)->where('status', 0)->where('expire_time', '>', time());
            if (get_sys_config('deduct_test_order_rate') == 'close') {
                $orderQuery = $orderQuery->where('is_test', '<>', 1);
            }
            $orderMoney = fenToYuan($orderQuery->sum('money'));
            $orderFee = round($orderMoney * $orderRate, 2);
            $moneyFee = round($orderRate * $params['money'], 2);
            $minRateAmount = floatval(get_sys_config('min_rate_amount'));
            if (0 < $minRateAmount && $moneyFee < $minRateAmount) {
                $moneyFee = $minRateAmount;
            }
            if (0 < $orderFee + $moneyFee) {
                $maxArrearageMoney = floatval(get_sys_config('max_arrearage_money') ?? 0);
                $balance = floatval($user->money);
                if ($balance < $orderFee + $moneyFee) {
                    $arrearage = $orderFee + $moneyFee - $balance;
                    if (0 < $maxArrearageMoney && $maxArrearageMoney < $arrearage) {
                        throw new \Exception('商户欠费金额已超过最高限额' . $maxArrearageMoney . '元');
                    }
                    if ($maxArrearageMoney <= 0) {
                        throw new \Exception('商户余额不足');
                    }
                }
            }
        }
    }
    public static function generateorderno(string|null $prefix = 'Y'): string
    {
        $date = date('YmdHis');
        $rand = mt_rand(10000, 99999);
        return strtoupper($prefix) . $date . $rand;
    }
    public function deductfee(\app\admin\model\Order|null $order = null): bool
    {
        $user = \app\admin\model\User::find($order->user_id);
        $rateService = new \app\common\service\RateService($user);
        $channelId = 0;
        if (!empty($order->channel->merchant->ec->id)) {
            $channelId = $order->channel->merchant->ec->id;
        }
        try {
            $rate = floatval($rateService->getUserRate($channelId)) / 100;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        $feeAmount = round($rate * $order->money, 2);
        $minRateAmount = floatval(get_sys_config('min_rate_amount'));
        if ($feeAmount <= $minRateAmount) {
            $feeAmount = $minRateAmount;
        }
        if ($feeAmount <= 0) {
            $feeAmount = 0;
        }
        if ($order->is_test == 1) {
            $deductTestOrderRate = get_sys_config('deduct_test_order_rate');
            if ($deductTestOrderRate == 'close') {
                $feeAmount = 0;
            }
        }
        $log = \app\admin\model\UserMoneyLog::where('user_id', $order->user_id)
            ->where('type', \app\admin\model\UserMoneyLog::TYPE_ORDER_FEE)
            ->where('content', $order->order_no)
            ->find();
        if ($log) {
            return true;
        }
        if (0 < $feeAmount) {
            $maxArrearageMoney = floatval(get_sys_config('max_arrearage_money') ?? 0);
            $balance = floatval($user->money);
            if ($balance < $feeAmount) {
                $arrearage = $feeAmount - $balance;
                if (0 < $maxArrearageMoney && $maxArrearageMoney < $arrearage) {
                    throw new \Exception('商户欠费金额已超过最高限额' . $maxArrearageMoney . '元');
                }
                if ($maxArrearageMoney <= 0) {
                    throw new \Exception('商户余额不足');
                }
            }
        }
        \think\facade\Db::startTrans();
        try {
            \app\admin\model\UserMoneyLog::create([
                'user_id' => $order->user_id,
                'money' => $feeAmount * -1,
                'memo' => '扣除订单手续费' . $feeAmount . '元',
                'type' => \app\admin\model\UserMoneyLog::TYPE_ORDER_FEE,
                'content' => $order->order_no,
            ]);
            \think\facade\Db::commit();
        } catch (\Exception $e) {
            \think\facade\Db::rollback();
            throw new \Exception('订单扣除手续费失败');
        }
        return true;
    }
    public function checksign(array|null $params = null, \app\admin\model\User|null $user = null): void
    {
        if (!\app\common\library\SignUtil::verifySign($params, $user->key, $params['sign_type'] ?? 'md5')) {
            throw new \Exception('签名验证失败');
        }
    }
    private function filterordername(string|null $name = null): string
    {
        return preg_replace('/[^\x{4e00}-\x{9fa5}\dA-Za-z*&$#%.￥@！~_\-=+]/u', '', $name);
    }
}

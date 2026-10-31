<?php

// 还原自 Swoole Compiler 加密文件: AlipayScan.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/payNotify/AlipayScan.php
//
// [2026-09-13 opcode 全量对照还原] 支付宝当面付(扫码)回调：
//   res($msg) = echo $msg; exit;  —— 回调协议响应（支付宝要求返回 success/fail 文本）
//   主流程: post 取参 → out_trade_no 判空 → 订单定位 → 重复回调短路(STATUS_PAID) →
//           trade_status 校验(TRADE_SUCCESS/TRADE_FINISHED) → bccomp 金额校验(2位精度) →
//           渠道账户定位 + 渠道类型校验(alipay_scan) → 渠道 callback 验签处理 →
//           OrderService::callback 订单完成 → res('success')

namespace app\api\controller\payNotify;

use app\admin\model\Order;
use app\common\service\channel\ChannelFactory;
use app\common\service\OrderService;

class AlipayScan extends \app\common\controller\Frontend
{
    protected array $noNeedLogin = ['*'];

    public function payCallback(): void
    {
        $params = request()->post('', null, null);

        if (empty($params['out_trade_no'])) {
            $this->res('fail');
        }

        $order = Order::where('order_no', $params['out_trade_no'] ?? '')->find() ?? null;
        if (empty($order)) {
            $this->res('fail');
        }

        // 重复回调短路
        if ($order->status == Order::STATUS_PAID) {
            $this->res('success');
        }

        $tradeStatus = $params['trade_status'] ?? '';
        if ($tradeStatus !== 'TRADE_SUCCESS' && $tradeStatus !== 'TRADE_FINISHED') {
            $this->res('fail');
        }

        // 金额一致性校验（2 位小数精度）
        if (bccomp((string) ($params['total_amount'] ?? '0'), (string) $order->money, 2) !== 0) {
            $this->res('fail');
        }

        $account = \app\admin\model\channel\Account::where('id', $order->channel_id)->find() ?? null;
        if (empty($account) || $account->channel_type !== 'alipay_scan') {
            $this->res('fail');
        }

        $channel = ChannelFactory::create($account) ?? null;
        $res = $channel->callback($order);
        if (!$res) {
            $this->res('fail');
        }

        (new OrderService())->callback($order);

        $this->res('success');
    }

    private function res($msg)
    {
        echo $msg;
        exit;
    }
}

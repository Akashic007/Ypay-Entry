<?php

// 还原自 Swoole Compiler 加密文件: Upstream.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/payNotify/Upstream.php

namespace app\api\controller\payNotify;

class Upstream extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['*'];
    public function pay(): void
    {
    $orderNo = $this->request->param('order_no');
    $tradeStatus = $this->request->param('trade_status');
    $order = \app\admin\model\Order::where('order_no', $orderNo)->find();
    if (empty($order)) {
        throw new \ba\Exception('订单不存在');
    }
    if (empty($tradeStatus) || $tradeStatus != 'TRADE_SUCCESS') {
        throw new \ba\Exception('订单未支付成功');
    }
    $upstream = \app\admin\model\supply\Upstream::find($order->channel->source_id);
    if (empty($upstream)) {
        throw new \ba\Exception('上游不存在');
    }
    $payment = new \app\common\library\upstream\Payment($upstream);
    $response = $payment->queryOrder($order->order_no);
    if (!\app\common\library\SignUtil::verifySign($response, $upstream->api_key)) {
        throw new \ba\Exception('验签失败');
    }
    if ($order->status == 1) {
        throw new \ba\Exception('订单已回调');
    }
    $orderService = new \app\common\service\OrderService();
    try {
        $orderService->callback($order);
    } catch (\Exception $e) {
        echo 'error：' . $e->getMessage();
        exit;
    }
    echo 'success';
    exit;
    }

    public function refund(): void
    {
    return;
    }

}

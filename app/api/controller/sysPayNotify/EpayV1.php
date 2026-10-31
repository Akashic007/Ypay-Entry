<?php

// 还原自 Swoole Compiler 加密文件: EpayV1.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/sysPayNotify/EpayV1.php

namespace app\api\controller\sysPayNotify;

class EpayV1 extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['*'];
    public function initialize(): void
    {
    parent::initialize();
    }
    public function notifyurl(): void
    {
    $this->request->filter('trim');
    $params = $this->request->param();
    if (empty($params['out_trade_no'])) {
        $this->response('fail');
    }
    if (empty($params)) {
        $this->response('fail');
    }
    $order = \app\admin\model\sys\Order::where('order_no', $params['out_trade_no'])->find();
    if (empty($order)) {
        $this->response('fail');
    }
    $orderService = new \app\common\service\sys\OrderService();
    try {
        $orderService->callback($order, $params);
    } catch (\Exception $e) {
        $this->response('fail');
    }
    $this->response();
    }
    public function returnurl(): \think\response\Redirect
    {
    $this->request->filter('trim');
    $params = $this->request->param();
    if (empty($params['out_trade_no'])) {
        return redirect(generateNoticeUrl('订单号为空'));
    }
    $order = \app\admin\model\sys\Order::where('order_no', $params['out_trade_no'])->find();
    if (empty($order)) {
        return redirect(generateNoticeUrl('订单不存在'));
    }
    $orderService = new \app\common\service\sys\OrderService();
    try {
        $orderService->callback($order, $params);
    } catch (\Exception $e) {
        return redirect(generateNoticeUrl('支付失败'));
    }
    return redirect(generateNoticeUrl('支付成功', 'success'));
    }
    // [opcode 对照] RECV_INIT 默认值 'success' 为 opcode 真值，签名补回默认参数
    public function response($msg = 'success'): void
    {
    echo $msg;
    exit;
    }
}

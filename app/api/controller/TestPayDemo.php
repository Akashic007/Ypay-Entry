<?php

// 还原自 Swoole Compiler 加密文件: TestPayDemo.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/TestPayDemo.php

namespace app\api\controller;

class TestPayDemo extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['*'];
// [opcode 还原] DEMO 下单：test_pay_config → post(name,type,money) → 组单+DEMO单号 → 签名 → createOrder+pay → pay_url/pay_info
    public function createorder(): void
    {
        $config = get_sys_config('test_pay_config');
        $data = $this->request->post(['name', 'type', 'money']);
        if (empty($data['type'])) {
            $this->error('请选择支付方式');
        }
        $order = [
            'pid'          => $config['pid'],
            'type'         => $data['type'],
            'out_trade_no' => \app\common\service\OrderService::generateOrderNo('DEMO'),
            'notify_url'   => $this->request->domain() . '/notify/demo/notifyCallback',
            'return_url'   => $this->request->domain() . '/notify/demo/callback',
            'name'         => $data['name'] ?? 'DEMO支付测试',
            'money'        => $data['money'] ?? 0.01,
            'is_test'      => 1,
        ];
        $order['sign'] = \app\common\library\SignUtil::generateSign($order, $this->auth->getUser()['key']);
        $orderService = new \app\common\service\OrderService();
        try {
            $orderInfo = $orderService->createOrder($order);
            $payService = new \app\common\service\PayService();
            $payService->pay($orderInfo);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $payUrl = $this->request->domain() . '/payment/getPay?order_no=' . $orderInfo['order_no'];
        $this->success('ok', [
            'pay_url'  => $payUrl,
            'pay_info' => $orderInfo['pay_info'],
        ]);
    }


// [opcode 还原] DEMO 同步回跳：trim 全参 → 空单号/无订单/未支付 redirect 通知页，成功带 success 态
    public function returncallback(): \think\response\Redirect
    {
        $params = $this->request->filter('trim')->param();
        if (empty($params['out_trade_no'])) {
            return redirect(generateNoticeUrl('订单号为空'));
        }
        $order = \app\admin\model\Order::where('order_no', $params['out_trade_no'])->find();
        if (!$order) {
            return redirect(generateNoticeUrl('订单不存在'));
        }
        if ($order->status == 1) {
            return redirect(generateNoticeUrl('支付成功', 'success'));
        }
        return redirect(generateNoticeUrl('订单未支付'));
    }


    public function notifycallback(): void
    {
    echo 'success';
    exit;
    }

}

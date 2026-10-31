<?php

// 还原自 Swoole Compiler 加密文件: Payment.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Payment.php

namespace app\api\controller;

class Payment extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['mapi', 'getpay', 'submit', 'pollingorder', 'wxpaySpecialJsapiPay'];
    public function mapi(): \think\response\Json
    {
        $this->request->filter('trim');
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请使用POST请求']);
        }

        $params = $this->request->post();
        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== '';
        });

        $orderService = new \app\common\service\OrderService();
        try {
            $order = $orderService->createOrder($params);

            $payService = new \app\common\service\PayService();
            $payService->pay($order);
        } catch (\Exception $e) {
            return json([
                'code' => 0,
                'msg'  => $e->getMessage(),
            ]);
        }

        return json(array_merge([
            'code'     => 1,
            'msg'      => 'success',
            'trade_no' => $order->order_no,
            'money'    => $order->pay_money,
        ], $order->pay_info));
    }

    public function submit(): \think\response\Redirect
    {
        $this->request->filter('trim');
        $params = $this->request->param();
        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== '';
        });

        $user = \app\admin\model\User::find($params['pid']);
        if (empty($user)) {
            return errorView('商户不存在');
        }

        $order        = null;
        $orderService = new \app\common\service\OrderService();
        try {
            $order = $orderService->createOrder($params);

            $payService = new \app\common\service\PayService();
            $payService->pay($order);
        } catch (\Exception $e) {
            $config = $user->userConfig['basic'];
            $ex     = [];
            if (isset($config['timeout_jump_type']) && $config['timeout_jump_type'] == 'custom' && !empty($config['timeout_url'])) {
                $ex['url'] = $config['timeout_url'];
            } else {
                $ex['url'] = $this->getSourceDomainUrl($order, $params);
            }
            return errorView($e->getMessage(), '发生错误', 0, $ex);
        }

        return redirect('/payment/getPay?order_no=' . $order->order_no);
    }

    private function getsourcedomainurl($order, array|null $params = null): string
    {
        if ($order) {
            $url = $order->return_url ?? $order->notify_url;
        } else {
            $url = $params['return_url'] ?? $params['notify_url'] ?? '';
        }

        $arr = parse_url($url);
        if (isset($arr['scheme']) && isset($arr['host'])) {
            return $arr['scheme'] . '://' . $arr['host'];
        }
        return $url;
    }

    public function getpay(): string
    {
        $order_no = $this->request->param('order_no');
        if (empty($order_no)) {
            return errorView('订单号为空');
        }

        $order = \app\admin\model\Order::where(['order_no' => $order_no])->with(['channel'])->find();
        if (empty($order)) {
            return errorView('订单不存在');
        }
        if ($order->expire_time < time()) {
            return errorView('订单已过期');
        }

        $order->create_time = date('Y-m-d H:i:s', $order->create_time);
        if (isset($order->pay_info['qrcode'])) {
            $payInfo           = $order->pay_info;
            $payInfo['qr_url'] = buildQrcodeUrl($order->pay_info['qrcode']);
            $payInfo['h5_url'] = $order->pay_info['h5_url'] ?? '';
            $order->pay_info   = $payInfo;
        }

        $config      = get_sys_config('', 'basics');
        $userConfig  = \app\admin\model\user\Config::where('user_id', $order->user_id)->find();
        $payTemplate = $userConfig->basic['pay_template'] ?? 'console';

        $templateDir = root_path('public') . 'views' . DIRECTORY_SEPARATOR . 'pay-template' . DIRECTORY_SEPARATOR . $payTemplate;
        if (!is_dir($templateDir)) {
            return errorView('支付模板不存在：' . $payTemplate);
        }

        \think\facade\View::assign([
            'order' => $order,
            'site'  => $config,
            'basic' => $userConfig->basic ?? [],
        ]);
        return \think\facade\View::fetch('pay-template/' . $payTemplate . '/index');
    }

    public function pollingorder(): void
    {
        $order_no = $this->request->param('order_no');
        if (empty($order_no)) {
            $this->error('订单号为空');
        }
        $order = \app\admin\model\Order::where(['order_no' => $order_no])->find();
        if (empty($order)) {
            $this->error('订单不存在');
        }

        $user   = \app\common\model\User::find($order->user_id);
        $config = $user->userConfig['channel_config'] ?? [];

        if ($order->expire_time < time()) {
            if (isset($config['timeout_jump_type']) && $config['timeout_jump_type'] == 'custom' && !empty($config['timeout_url'])) {
                $url = $config['timeout_url'];
            } else {
                $url = $order->return_url;
                if (empty($url)) {
                    $url = $order->notify_url;
                }
                $url = parse_url($url);
                if (isset($url['scheme']) && isset($url['host'])) {
                    $url = $url['scheme'] . '://' . $url['host'];
                } else {
                    $url = $order->notify_url;
                }
            }
            $this->error('订单已过期', ['url' => $url]);
        }

        if ($order->status == 1) {
            $orderService = new \app\common\service\OrderService();
            if ($order->notify_status != 1) {
                $orderService->callback($order);
            }
            try {
                $url = $orderService->getNotifyUrl($order);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                return;
            }
            $this->success('订单已支付', ['url' => $url['return']]);
        }

        $payInfo                = $order->pay_info;
        $payInfo['expire_time'] = $order->expire_time - time();
        if (isset($order->pay_info['qrcode'])) {
            $payInfo['qr_url'] = buildQrcodeUrl($order->pay_info['qrcode']);
        }
        $this->success('支付信息获取成功', $payInfo, 2);
    }

}

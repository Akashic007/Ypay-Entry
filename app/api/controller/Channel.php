<?php

// 还原自 Swoole Compiler 加密文件: Channel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Channel.php

namespace app\api\controller;

class Channel extends \app\common\controller\Frontend {
    protected array $middleware = ['app\\common\\middleware\\YAuthCheck' => ['only' => ['add', 'edit'], ], 'app\\common\\middleware\\UserCheck'];
    public function initialize(): void
    {
        parent::initialize();
    }
    public function getchannellist(): void
    {
        $todayStart     = date('Y-m-d 00:00:00');
        $todayEnd       = date('Y-m-d 23:59:59');
        $yesterdayStart = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
        $yesterdayEnd   = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));
        $weekStart      = strtotime(date('Y-m-d 00:00:00', strtotime('monday this week')));
        $weekEnd        = date('Y-m-d 23:59:59');
        $monthStart     = date('Y-m-01 00:00:00');
        $monthEnd       = date('Y-m-t 23:59:59');

        $limit = $this->request->param('limit', 10) ?? 10;
        $list  = \app\admin\model\channel\Account::where('user_id', $this->auth->id)
            ->where('source_type', '<>', 'down')
            ->with(['channelTypeTable' => function ($query) {
                $query->field(['id', 'type']);
            }])
            ->withSum(['todayOrder' => function ($query) {
                $query->where('status', 1)->where('is_test', 0);
            }], 'pay_money')
            ->withSum(['yesterdayOrder' => function ($query) {
                $query->where('status', 1)->where('is_test', 0);
            }], 'pay_money')
            ->withSum(['weekOrder' => function ($query) {
                $query->where('status', 1)->where('is_test', 0);
            }], 'pay_money')
            ->withSum(['monthOrder' => function ($query) {
                $query->where('status', 1)->where('is_test', 0);
            }], 'pay_money')
            ->order('weigh', 'desc')
            ->order('id', 'desc')
            ->paginate($limit)
            ->each(function ($item) {
                $item->today_order_sum     = fenToYuan((int) $item->today_order_sum);
                $item->yesterday_order_sum = fenToYuan((int) $item->yesterday_order_sum);
                $item->week_order_sum      = fenToYuan((int) $item->week_order_sum);
                $item->month_order_sum     = fenToYuan((int) $item->month_order_sum);
            });
        $this->success('ok', $list);
    }
    public function add(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方法错误！');
        }
        $data = $this->request->post();
        $user = \app\admin\model\User::find($this->auth->id);
        $service = new \app\common\service\ChannelService();
        try {
            $account = $service->create($user, $data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('新增成功', $account);
    }
    public function edit(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方法错误！');
        }
        $id   = $this->request->param('id');
        $data = $this->request->post(['config', 'status', 'remark', 'weigh']);
        $data = array_filter($data, function ($value) {
            return $value !== '' && $value !== null;
        });
        if (empty($id)) {
            $this->error('通道账户ID为空');
        }
        if (empty($data)) {
            $this->error('参数为空');
        }
        $account = \app\admin\model\channel\Account::where([
            'id'      => $id,
            'user_id' => $this->auth->id,
        ])->find();
        if (empty($account)) {
            $this->error('通道不存在');
        }
        $params = [];
        if (!empty($data['config'])) {
            foreach ($data['config'] as $key => $value) {
                if ($value !== '' && $value !== null) {
                    $params[$key] = $value;
                }
            }
        }
        if (isset($data['config'])) {
            $data['config'] = $params;
        }
        $service = new \app\common\service\ChannelService();
        try {
            $service->edit(\app\admin\model\User::find($this->auth->id), $account, $data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('更新成功');
    }
    public function setchannelstatus(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('ID为空');
        }
        $account = \app\admin\model\channel\Account::find($id);
        if (empty($account)) {
            $this->error('通道不存在');
        }
        $account->status = $account->status == 1 ? 0 : 1;
        $account->save();
        $this->success('更新成功', [
            'status' => $account->status,
        ]);
    }
    public function del(): void
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $this->error('ID为空');
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $accounts = \app\admin\model\channel\Account::where('user_id', $this->auth->id)
            ->where('id', 'in', $ids)
            ->select();
        $ok      = 0;
        $message = 'success';
        $service = new \app\common\service\ChannelService();
        foreach ($accounts as $account) {
            try {
                $service->delete($account);
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
            $ok++;
        }
        if ($ok <= 0) {
            $this->error('删除失败：' . $message);
        }
        $this->success('删除成功 ' . $ok . ' 条');
    }
    public function getpaytype(): void
    {
        $list = \app\admin\model\channel\PayType::where('status', 1)
            ->order('weigh', 'desc')
            ->select()
            ->toArray();
        $this->success('ok', $list);
    }
    public function gettype(): void
    {
        $payType = $this->request->param('pay_type');
        $list = \app\admin\model\channel\Type::where([
            'status'   => 1,
            'pay_type' => $payType,
        ])
            ->order('weigh', 'desc')
            ->select()
            ->toArray();
        $this->success('ok', $list);
    }
    public function getmchlist(): void
    {
        $platformCode = $this->request->param('platform_code');
        $list = \app\admin\model\merchant\Merchant::where('user_id', $this->auth->id)
            ->with(['sp' => function ($query) {
                $query->field(['id']);
            }])
            ->where('status', 1)
            ->where('platform_code', $platformCode)
            ->field(['id', 'name', 'platform_code'])
            ->order('id', 'desc')
            ->select();
        $this->success('ok', $list->toArray());
    }
    public function testpay(): void
    {
        // [2026-10-02 opcode 对照修正] 原版取参 ['money','id']（32B 桶直读 LIT3）——
        // 还原版只取了 ['money'] 导致 $data['id'] 恒空、测试支付恒"参数错误"
        $data = $this->request->param(['money', 'id']);
        \think\facade\Log::debug('[testPay-debug] get=' . json_encode(request()->get()) . ' param=' . json_encode($data) . ' route=' . request()->pathinfo());
        if (empty($data['money']) || empty($data['id'])) {
            $this->error('参数错误');
        }
        if ($data['money'] <= 0) {
            $this->error('金额错误');
        }
        $account = \app\admin\model\channel\Account::where([
            'id'      => $data['id'],
            'user_id' => $this->auth->id,
        ])->find();
        if (empty($account)) {
            $this->error('通道不存在');
        }
        if ($account->status != 1 || $account->online_status != 1) {
            $this->error('通道状态异常');
        }
        $order = [
            'pid'          => $this->auth->id,
            'type'         => $account->pay_type,
            'out_trade_no' => \app\common\service\OrderService::generateOrderNo('TEST'),
            'notify_url'   => $this->request->domain() . '/notify/payTest',
            'return_url'   => $this->request->domain() . '/channelList',
            'name'         => '通道支付测试',
            'money'        => $data['money'] ?? 0.01,
            'is_test'      => 1,
            'channel_id'   => $account->id,
        ];
        $order['sign'] = \app\common\library\SignUtil::generateSign($order, $this->auth->getUser()['key']);
        $orderService  = new \app\common\service\OrderService();
        try {
            $sysOrder   = $orderService->createOrder($order);
            $payService = new \app\common\service\PayService();
            $payService->pay($sysOrder);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $payUrl = $this->request->domain() . '/payment/getPay?order_no=' . $sysOrder->order_no;
        $this->success('ok', [
            'pay_url'  => $payUrl,
            'pay_info' => $sysOrder->pay_info,
        ]);
    }
}

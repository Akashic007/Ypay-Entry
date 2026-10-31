<?php

// 还原自 Swoole Compiler 加密文件: SysOrder.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/SysOrder.php

namespace app\api\controller;

class SysOrder extends \app\common\controller\Frontend {
    protected array $middleware = ['app\\common\\middleware\\YAuthCheck' => ['only' => ['createOrder']]];
    public function initialize(): void
    {
        parent::initialize();
    }

    public function getorderlist(): void
    {
        $limit = $this->request->param('limit', 10) ?? 10;
        $list  = \app\admin\model\sys\Order::where('user_id', $this->auth->id)
            ->order('id', 'desc')
            ->paginate($limit);
        $this->success('ok', $list);
    }

    public function getsyspaymethod(): void
    {
        $list = \app\admin\model\sys\PayChannel::where('status', 1)
            ->field(['pay_type'])
            ->group('pay_type')
            ->order('weigh', 'desc')
            ->select()
            ->toArray();
        $list = array_map(function ($item) {
            switch ($item['pay_type']) {
                case 'alipay':
                    $name = '支付宝';
                    break;
                case 'qqpay':
                    $name = 'QQ支付';
                    break;
                default:
                    $name = '微信支付';
            }
            return [
                'type' => $item['pay_type'],
                'name' => $name,
            ];
        }, $list);
        $this->success('ok', $list);
    }

    public function createorder(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式');
        }
        $data = $this->request->post();
        if (empty($data['type'])) {
            $this->error('订单类型为空');
        }
        $service = new \app\common\service\sys\OrderService();
        try {
            $order = $service->create($data, $this->auth->getUser());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('订单创建成功', $order);
    }

    public function query(): void
    {
        $orderNo = $this->request->param('order_no');
        if (empty($orderNo)) {
            $this->error('订单号为空');
        }
        $order = \app\admin\model\sys\Order::where('order_no', $orderNo)->find();
        if (empty($order)) {
            $this->error('订单不存在');
        }
        $this->success('查询成功', $order);
    }

}

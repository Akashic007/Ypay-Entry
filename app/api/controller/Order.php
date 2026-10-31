<?php

// 还原自 Swoole Compiler 加密文件: Order.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Order.php

namespace app\api\controller;

class Order extends \app\common\controller\Frontend {
    protected array $middleware = ['app\\common\\middleware\\YAuthCheck' => ['only' => ['manual', 'reNotify'], ], 'app\\common\\middleware\\UserCheck'];
    public function initialize(): void
    {
        parent::initialize();
    }

    public function getorderlist(): void
    {
        $limit = (int) ($this->request->param('limit', 10) ?? 10);
        $params = $this->request->param(['status', 'type', 'name', 'order_no', 'out_trade_no']);
        $query = \app\admin\model\Order::where('user_id', $this->auth->id)
            ->with([
                'channel' => function ($query) {
                    $query->field(['channel_type'])->with(['channelTypeTable']);
                },
            ])
            ->order('id', 'desc');
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (isset($params['type']) && trim((string) $params['type']) !== '') {
            $query->where('type', trim((string) $params['type']));
        }
        $name = trim((string) ($params['name'] ?? ''));
        if ($name !== '') {
            $query->whereLike('name', '%' . str_replace('%', '\%', $name) . '%');
        }
        $orderNo = trim((string) ($params['order_no'] ?? ''));
        if ($orderNo !== '') {
            $query->whereLike('order_no', '%' . str_replace('%', '\%', $orderNo) . '%');
        }
        $outTradeNo = trim((string) ($params['out_trade_no'] ?? ''));
        if ($outTradeNo !== '') {
            $query->whereLike('out_trade_no', '%' . str_replace('%', '\%', $outTradeNo) . '%');
        }
        $payTimeStart = trim((string) ($params['pay_time_start'] ?? ''));
        $payTimeEnd = trim((string) ($params['pay_time_end'] ?? ''));
        if ($payTimeStart !== '' || $payTimeEnd !== '') {
            $query->where('pay_time', '>', 0);
            if ($payTimeStart !== '') {
                $query->whereTime('pay_time', '>=', $payTimeStart);
            }
            if ($payTimeEnd !== '') {
                $query->whereTime('pay_time', '<=', $payTimeEnd);
            }
        }
        $createTimeStart = trim((string) ($params['create_time_start'] ?? ''));
        $createTimeEnd = trim((string) ($params['create_time_end'] ?? ''));
        if ($createTimeStart !== '') {
            $query->whereTime('create_time', '>=', $createTimeStart);
        }
        if ($createTimeEnd !== '') {
            $query->whereTime('create_time', '<=', $createTimeEnd);
        }
        $list = $query->paginate($limit);
        $this->success('ok', $list);
    }

    public function manual(): void
    {
        $manual = get_sys_config('user_manual') ?? 'close';
        if ($manual != 'open') {
            $this->error('暂为开启手动补单');
        }
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('订单ID为空');
        }
        $order = \app\admin\model\Order::find($id);
        if (empty($order)) {
            $this->error('订单不存在');
        }
        if ($order->status != 0) {
            $this->error('只有未支付的订单才能补单');
        }
        if ($order->user_id != $this->auth->id) {
            $this->error('订单不属于当前用户');
        }
        try {
            $service = new \app\common\service\OrderService();
            $service->callback($order);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }

    public function renotify(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('订单ID为空');
        }
        $order = \app\admin\model\Order::find($id);
        if (empty($order)) {
            $this->error('订单不存在');
        }
        if ($order->user_id != $this->auth->id) {
            $this->error('订单不属于当前用户');
        }
        if ($order->status != 1) {
            $this->error('未支付订单无法通知');
        }
        try {
            $service = new \app\common\service\OrderService();
            $service->notifyCallback($order);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('通知成功');
    }

    public function refund(): void
    {
        $orderNo = $this->request->param('order_no');
        if (empty($orderNo)) {
            $this->error('订单号为空');
        }
        $order = \app\admin\model\Order::where('order_no', $orderNo)->find();
        if (empty($order)) {
            $this->error('订单不存在');
        }
        if ($order->user_id != $this->auth->id) {
            $this->error('订单不属于当前用户');
        }
        if ($order->status != 1 && $order->status != 2) {
            $this->error('当前订单状态无法退款');
        }
        $data = $this->request->param(['amount']);
        try {
            $service = new \app\common\service\OrderService();
            $service->createRefund($order, $data['amount'], ['refund_reason' => $data['reason']]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('申请退款成功');
    }

    public function getrefundinfo(): void
    {
        $orderNo = $this->request->param('order_no');
        if (empty($orderNo)) {
            $this->error('订单号为空');
        }
        $order = \app\admin\model\Order::where('order_no', $orderNo)->find();
        if (empty($order)) {
            $this->error('订单不存在');
        }
        if ($order->user_id != $this->auth->id) {
            $this->error('订单不属于当前用户');
        }
        $records = \app\admin\model\RefundRecord::where('order_no', $orderNo)->whereIn('status', [0]);
        $this->success('ok', [
            'count'      => $records->count(),
            'sum'        => fenToYuan($records->sum('refund_amount')),
            'refundable' => fenToYuan(yuanToFen($order->pay_money) - (int) $records->sum('refund_amount')),
        ]);
    }

}

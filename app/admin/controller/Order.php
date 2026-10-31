<?php

// 还原自 Swoole Compiler 加密文件: Order.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/Order.php

namespace app\admin\controller;

class Order extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', null, 'extend_param'];
    // [opcode 对照] 属性默认值数组原 2 槽（第 2 槽被清零），index/visible 引用 channel 关联，补全为 'channel'
    protected array $withJoinTable = ['user', 'channel'];
    // [opcode 对照] 属性默认值数组原 5 槽（'id'/'order_no'/'out_trade_no' 可见，另 2 槽被清零），
    // 按订单页检索字段（订单名称/交易单号）补全为 'name' 与 'trade_no'（留 null 会使快速搜索拼出非法列 "order."）
    protected string|array $quickSearchField = ['id', 'name', 'order_no', 'trade_no', 'out_trade_no'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Order();
    }

    public function index(): void
    {
        // [opcode 对照] 本类覆写 index()（前次反编译整方法丢失，按 opcode 全量补齐）：
        // param('select')->select()；list(queryBuilder)；withJoin(withJoinTable, withJoinType)
        // ->visible(['user'=>['username'],'channel'=>['channel_type','channel_type_name']])
        //   （channel 第二字段原槽位被清零，据前端 index-*.js 读取 channel.channel_type_name 且
        //    visible 白名单外字段会被隐藏推断补全）->alias->where->order->paginate
        // ->success('', ['list' => appendChannelTypeName(items()), 'total', 'remark' => get_route_remark()])
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->visible([
                'user'    => ['username'],
                'channel' => ['channel_type', 'channel_type_name'],
            ])
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $this->appendchanneltypename($res->items()),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }


    protected function appendchanneltypename(array|null $list = null): array
    {
        // opcode 真值：whereIn/数组 unique/column/字段名均为字面量解密结果
        $typeNames = [];
        foreach ($list as $item) {
            $channelType = $item->channel->channel_type ?? null;
            if (empty($channelType)) {
                continue;
            }
            $typeNames[] = $channelType;
        }
        if (empty($typeNames)) {
            return $list;
        }
        $typeList = \app\admin\model\channel\Type::whereIn('type', array_unique($typeNames))->column('name', 'type');
        foreach ($list as $item) {
            $channel     = $item->channel ?? null;
            $channelType = $channel->channel_type ?? null;
            if (empty($channelType)) {
                continue;
            }
            $channel->channel_type_name = $typeList[$channelType] ?? $channelType;
        }
        return $list;
    }

    public function edit(): void
    {
        // 订单不允许编辑（opcode：方法体仅 RETURN）
        return;
    }

    public function refund(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('订单ID为空');
        }
        $order = \app\admin\model\Order::find($id);
        if (empty($order)) {
            $this->error('订单不存在');
        }
        // [opcode 对照] param 数组原 2 槽（第 2 槽被清零），据后文 $data['reason'] 补全为 'reason'
        $data = $this->request->param(['amount', 'reason']);
        try {
            $service = new \app\common\service\OrderService();
            $service->createrefund($order, $data['amount'], ['refund_reason' => $data['reason']]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('申请退款成功');
    }

    public function getrefundinfo(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('订单ID为空');
        }
        $order = \app\admin\model\Order::find($id);
        if (empty($order)) {
            $this->error('订单不存在');
        }
        $service    = new \app\common\service\OrderService();
        $refundInfo = $service->queryrefundrecode($order);
        $this->success('ok', [
            'count'      => $refundInfo['refund_num'],
            'sum'        => $refundInfo['refund_amount'],
            'refundable' => $refundInfo['refundable_amount'],
        ]);
    }

    public function manual(): void
    {
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
        if ($order->expire_time < time()) {
            $this->error('订单已过期');
        }
        try {
            $service = new \app\common\service\OrderService();
            $service->callback($order);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('补单成功');
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
        if ($order->status != 1) {
            $this->error('未支付订单无法通知');
        }
        try {
            $service = new \app\common\service\OrderService();
            $service->notifycallback($order);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('通知成功');
    }

    public function cleardata(): void
    {
        // [opcode 对照] param 数组原 5 槽（'time'/'status'/'is_test' 可见，另 2 槽被清零），
        // 据方法体内读取的键补全为 'notify_status' 与 'expire'（白名单顺序不影响行为）
        $data = $this->request->param(['time', 'status', 'is_test', 'notify_status', 'expire']);
        if (empty($data['time'])) {
            $time = time();
        } elseif (is_numeric($data['time'])) {
            if (strlen($data['time']) === 13) {
                $time = intval($data['time'] / 1000);
            } else {
                $time = intval($data['time']);
            }
        } else {
            $time = strtotime($data['time']);
        }
        $query = $this->model->where('create_time', '<', $time);
        if (isset($data['is_test'])) {
            if (!in_array($data['is_test'], ['0', '1'])) {
                $this->error('是否是测试订单参数错误');
            }
            $query = $query->where('is_test', $data['is_test']);
        }
        if (isset($data['status'])) {
            // [opcode 对照] 原数组 6 槽（偶数位可见 '0','1','2'，奇数位被清零），按 Order::STATUS_* 值域 0-3 补全
            if (!in_array($data['status'], ['0', '1', '2', '3'])) {
                $this->error('订单状态参数错误');
            }
            $query = $query->where('status', $data['status']);
        }
        if (isset($data['notify_status'])) {
            // [opcode 对照] 原数组 3 槽（中间槽被清零），notify_status 值域 {0,1,2}（OrderNotifyJob 真值）
            if (!in_array($data['notify_status'], ['0', '1', '2'])) {
                $this->error('通知状态参数错误');
            }
            $query = $query->where('notify_status', $data['notify_status']);
        }
        if (isset($data['expire'])) {
            // [opcode 对照] 原数组 3 槽（中间槽被清零），下文仅区分 '1'=已过期 / '2'=未过期，补全为 0-2
            if (!in_array($data['expire'], ['0', '1', '2'])) {
                $this->error('到期状态参数错误');
            }
            if ($data['expire'] == '1') {
                $query = $query->where('expire_time', '<', time());
            }
            if ($data['expire'] == '2') {
                $query = $query->where('expire_time', '>', time());
            }
        }
        $query->delete();
        $this->success('清理成功');
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: Order.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/agent/Order.php

namespace app\api\controller\agent;

class Order extends \app\common\controller\Frontend {
    private \app\admin\model\agent\Agent $agent;
    public function initialize(): void
    {
        parent::initialize();

        $agent = \app\admin\model\agent\Agent::where('user_id', $this->auth->id)
            ->with(['subscribe' => function ($query) {
                $query->withoutField('price');
            }])
            ->find();
        if (!$agent) {
            $this->error('暂未开通代理');
        }
        if ($agent->status != 1) {
            $this->error('代理状态异常');
        }
        $this->agent = $agent;
    }

    public function getdownorderlist(): void
    {
        $limit = $this->request->param('limit', 10) ?? 10;
        $status = $this->request->param(['status']);
        $query = \app\admin\model\Order::where('user_id', $this->auth->id)
            ->with(['channel' => function ($query) {
                // [opcode 对照] field 数组第 2 元素被保护清除，按主键惯例补 'id'
                $query->field(['channel_type', 'id'])
                    ->with(['channelTypeTable' => function ($query) {
                        // [opcode 对照] ['id', <被清除>, 'name']，'type' 为下方 channel_type 输出必需
                        $query->field(['id', 'type', 'name']);
                    }]);
            }])
            ->where('source', 'down')
            ->order('id', 'desc');
        if (!empty($status['status'])) {
            $query->where('status', $status['status']);
        }
        $list = $query->paginate($limit)->each(function ($item) {
            $item->channel_name = $item['channel']['channelTypeTable']['name'] ?? '通道不存在';
            $item->channel_type = $item['channel']['channelTypeTable']['type'] ?? '通道不存在';
            unset($item['channel']);
        });
        $this->success('ok', $list);
    }

}

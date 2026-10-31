<?php

// 还原自 Swoole Compiler 加密文件: Downstream.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/agent/Downstream.php

namespace app\api\controller\agent;

class Downstream extends \app\common\controller\Frontend {
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

    public function getlist(): void
    {
        $list = \app\admin\model\supply\Downstream::where('agent_id', $this->agent->id)->select();
        $this->success('ok', $list);
    }

    public function getorderlist(): void
    {
        $limit = $this->request->param('limit', 10) ?? 10;
        $status = $this->request->param(['status']);
        $query = \app\admin\model\Order::where('user_id', $this->auth->id)
            ->with(['channel' => function ($query) {
                // [opcode 对照] field 数组第 2 元素被保护清除，按主键惯例补 'id'
                $query->field(['channel_type', 'id']);
            }])
            ->where('source', 'down')
            ->order('id', 'desc');
        if (!empty($status['status'])) {
            $query->where('status', $status['status']);
        }
        $list = $query->paginate($limit);
        $this->success('ok', $list);
    }

}

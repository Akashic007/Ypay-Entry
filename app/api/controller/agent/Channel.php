<?php

// 还原自 Swoole Compiler 加密文件: Channel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/agent/Channel.php

namespace app\api\controller\agent;

class Channel extends \app\common\controller\Frontend {
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
        $limit = $this->request->param('limit') ?? 10;
        $list = \app\admin\model\channel\Account::where('user_id', $this->auth->id)
            ->where('source_type', 'down')
            ->with(['channelTypeTable' => function ($query) {
                // [opcode 对照] field 数组中间元素被编译器保护清除，按 as_channel_type 表结构语义补 'name'
                $query->field(['id', 'name', 'type']);
            }])
            ->order('id', 'desc')
            ->paginate($limit);
        $this->success('ok', $list);
    }

}

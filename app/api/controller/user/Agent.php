<?php

// 还原自 Swoole Compiler 加密文件: Agent.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/user/Agent.php
//
// [2026-09-22 opcode 全量对照还原] 依据 dump_m.py 指令序重建全部方法：
//   initialize:   parent::initialize()
//   getparentagent: auth->getUser() + Agent::where('user_id', auth->id)
//                   ->with(['subscribe' => field 白名单]) ->field(['id','status','subscribe_id'])
//                   ->find() → success('ok', $agent)
//                   （受保护字面量: 外层 field 数组隐藏位='status'、闭包隐藏位='rebate_ratio'/'entry_discount'，
//                     均在解密后字面量池中且无其他引用，按池序回填）
//   applytoagent: isPost 校验 → invite_code 空/查无 → status!=1 → 自己的代理 →
//                   已绑定上级 → user.agent_id = agent.id → save → success('申请成功')

namespace app\api\controller\user;

class Agent extends \app\common\controller\Frontend {
    public function initialize(): void
    {
        parent::initialize();
    }

    public function getparentagent(): void
    {
        $user = $this->auth->getUser();
        $agent = \app\admin\model\agent\Agent::where('user_id', $this->auth->id)
            ->with(['subscribe' => function ($query) {
                $query->field(['id', 'rebate_ratio', 'name', 'entry_discount', 'status']);
            }])
            ->field(['id', 'status', 'subscribe_id'])
            ->find();
        $this->success('ok', $agent);
    }

    public function applytoagent(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式');
        }
        $inviteCode = $this->request->param('invite_code');
        if (empty($inviteCode)) {
            $this->error('邀请码为空');
        }
        $agent = \app\admin\model\agent\Agent::where('invite_code', $inviteCode)->find();
        if (empty($agent)) {
            $this->error('邀请码错误');
        }
        if ($agent['status'] != 1) {
            $this->error('代理状态异常');
        }
        if ($agent['user_id'] == $this->auth['id']) {
            $this->error('不能成为自己的代理');
        }
        $user = $this->auth->getUser();
        if ($user['agent_id'] != 0) {
            $this->error('请先与上级代理解除绑定');
        }
        $user->agent_id = $agent['id'];
        $user->save();
        $this->success('申请成功');
    }

}

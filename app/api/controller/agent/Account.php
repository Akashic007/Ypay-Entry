<?php

// 还原自 Swoole Compiler 加密文件: Account.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/agent/Account.php

namespace app\api\controller\agent;

class Account extends \app\common\controller\Frontend {
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

    public function getagentinfo(): void
    {
        $this->success('ok', $this->agent);
    }

    public function reset(): void
    {
        $type = $this->request->param('type');
        if (!$type) {
            $this->error('重置类型为空');
        }
        try {
            switch ($type) {
                // [opcode 对照] SWITCH_STRING 跳转表实证：原码 case 即交叉——'api_id' 调 resetApiKey、'api_key' 调 resetApiId
                case 'api_id':
                    $this->agent->resetApiKey($this->agent->id);
                    break;
                case 'api_key':
                    $this->agent->resetApiId($this->agent->id);
                    break;
                case 'invite_code':
                    $this->agent->resetInviteCode($this->agent->id);
                    break;
                default:
                    throw new \ba\Exception('重置类型错误');
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('重置成功', [$type => $this->agent->{$type}]);
    }

}

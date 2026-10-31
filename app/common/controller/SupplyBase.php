<?php

// 还原自 Swoole Compiler 加密文件: SupplyBase.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/controller/SupplyBase.php

namespace app\common\controller;

class SupplyBase extends \app\common\controller\Api {
    protected \app\admin\model\agent\Agent $agent;
    protected \app\admin\model\User $user;
    protected \app\admin\model\supply\Downstream $downstream;
    protected array $noSignMethod = [];
    protected array $noCheckAgentStatusMethod = [];
    public function initialize(): void
    {
        parent::initialize();
        $params = $this->request->param();
        if (empty($params['api_id'])) {
            $this->error('API ID为空');
        }
        $agent = \app\admin\model\agent\Agent::where('api_id', $params['api_id'])->find();
        if (empty($agent)) {
            $this->error('代理不存在');
        }
        if ($agent->status != 1) {
            $this->error('代理状态异常');
        }
        if (!in_array($this->request->action(), $this->noSignMethod)) {
            if (empty($params['sign'])) {
                $this->error('签名为空');
            }
            $params = array_filter($params, function ($value) {
                return !($value instanceof \CURLFile) && !is_array($value) && !is_object($value);
            });
            if (!\app\common\library\SignUtil::verifySign($params, $agent->api_key)) {
                $this->error('签名验证失败');
            }
        }
        if (!in_array($this->request->action(), $this->noCheckAgentStatusMethod)) {
            $downstream = \app\admin\model\supply\Downstream::where('agent_id', $agent->id)->find();
            if (empty($downstream)) {
                $this->error('无下游对接记录');
            }
            if ($downstream->status != 1) {
                $this->error('对接状态异常');
            }
            $this->downstream = $downstream;
        }
        $this->agent = $agent;
        $this->user = $this->agent->user;
        if (empty($this->user)) {
            $this->error('用户不存在！');
        }
    }

}

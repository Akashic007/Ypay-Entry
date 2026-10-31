<?php

// 还原自 Swoole Compiler 加密文件: Docking.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/supply/controller/Docking.php

namespace app\supply\controller;

class Docking extends \app\common\controller\SupplyBase {
    // [opcode 对照] default_properties 真值槽0='applyDocking'，槽1(RAW0) 按池序/本控制器另一动作语义还原为 'getDockingInfo'（SupplyBase::initialize 为区分大小写 in_array，客户端 SupplyService 以 camelCase URL 调用）
    protected array $noCheckAgentStatusMethod = ['applyDocking', 'getDockingInfo'];
    public function getDockingInfo(): void
    {
        $domain = $this->request->param('domain');
        $downstream = \app\admin\model\supply\Downstream::where('agent_id', $this->agent->id)->where('domain', $domain)->find();
        $this->success('查询成功', $downstream);
    }

    public function applyDocking(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式');
        }
        $data = $this->request->post(['domain']);
        if (empty($data['domain'])) {
            $this->error('域名为空');
        }
        if ($data['domain'] == request()->domain()) {
            $this->error('不能对接自己的站点！！');
        }
        if (!is_url($data['domain'])) {
            $this->error('请求域名错误');
        }
        $downstream = \app\admin\model\supply\Downstream::where('agent_id', $this->agent->id)->where('domain', $data['domain'])->find();
        $review = true;
        if (get_sys_config('is_down_review') == 'close') {
            $review = false;
        }
        if ($downstream) {
            $downstream->status = $review ? 0 : 1;
            $downstream->save();
        }
        $downstream = \app\admin\model\supply\Downstream::create([
            // [opcode 对照] 'PWT^A'/']T' 为加密短字面量（dec_literal 失真），按 agent_id 回填语义定案为 agent/id（与 getDockingInfo 的 where('agent_id', $this->agent->id) 同构）
            'agent_id' => $this->agent->id,
            'domain'   => $data['domain'],
            'status'   => $review ? 0 : 1,
        ]);
        $this->success('申请成功');
    }

}

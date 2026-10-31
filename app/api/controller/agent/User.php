<?php

// 还原自 Swoole Compiler 加密文件: User.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/agent/User.php

namespace app\api\controller\agent;

class User extends \app\common\controller\Frontend {
    private \app\admin\model\agent\Agent $agent;
    private array $topAgentMethod = ['getSecondDiscountList', null];
    public function initialize(): void
    {
        parent::initialize();

        $agent = \app\admin\model\agent\Agent::where('user_id', $this->auth->id)
            ->with(['subscribe' => function ($query) {
                $query->withoutField('price');
            }])
            ->find();
        if (empty($agent)) {
            $this->error('暂未开通代理');
        }
        if ($agent->status != 1) {
            $this->error('代理状态异常');
        }
        $this->agent = $agent;

        if (in_array($this->request->action(), $this->topAgentMethod) && $this->agent->subscribe->is_top != 1) {
            $this->error('非顶级代理无权操作');
        }
    }

    public function getlist(): void
    {
        $limit = $this->request->param('limit', 10) ?? 10;
        $outField = ['password', 'key', 'money', 'score', 'last_login_time', 'last_login_ip'];

        if ($this->request->param('is_agent') == 1) {
            $query = \app\common\model\User::hasWhere('agent', ['status' => 1])
                ->where('status', 'enable');
        } else {
            $query = \app\common\model\User::where('status', 'enable');
        }

        $list = $query->where('agent_id', $this->agent->id)
            ->with(['agent' => function ($query) {
                $query->field(['id', 'user_id'])
                    ->with(['subscribe' => function ($query) {
                        $query->field(['id', 'name']);
                    }]);
            }])
            ->paginate($limit)
            ->each(function ($item) use ($outField) {
                foreach ($outField as $field) {
                    unset($item[$field]);
                }
            });

        $this->success('ok', $list);
    }

    public function getseconddiscountlist(): void
    {
        $agentId = $this->request->param('agent_id');

        $agent = \app\admin\model\agent\Agent::hasWhere('user', function ($query) {
            $query->where('agent_id', $this->agent->id);
        })->find($agentId);
        if (empty($agent)) {
            $this->error('代理不存在');
        }

        $list = \app\admin\model\agent\UserEntryDiscount::where('agent_id', $agentId)
            ->where('status', 1)
            ->with([
                'agent' => function ($query) {
                    $query->field(['id', 'user_id']);
                },
                'ec' => function ($query) {
                    $query->field(['id', 'sp_id', 'platform_code'])
                        ->with(['sp' => function ($query) {
                            $query->field(['id', 'name']);
                        }]);
                },
                'user' => function ($query) {
                    $query->field(['id', 'agent_id']);
                },
            ])
            ->select()
            ->each(function ($item) {
                $item->channel = getEntryChannelName($item['ec']['platform_code'])['name'] . '[' . $item['ec']['sp']['name'] . ']';
            });

        $this->success('ok', $list);
    }

    public function setseconddiscount(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式');
        }

        $post = $this->request->post(['agent_id', 'channel_id', 'discount']);
        if (empty($post['agent_id'])) {
            $this->error('代理ID为空');
        }
        if (empty($post['channel_id'])) {
            $this->error('通道ID为空');
        }
        if (empty($post['discount'])) {
            $this->error('折扣为空');
        }

        $agent = \app\admin\model\agent\Agent::hasWhere('user', function ($query) {
            $query->where('agent_id', $this->agent->id);
        })->find($post['agent_id']);
        if (!$agent) {
            $this->error('代理不存在');
        }

        $channel = \app\admin\model\EntryChannel::find($post['channel_id']);
        if (empty($channel)) {
            $this->error('通道不存在');
        }

        $agentService = new \app\common\service\AgentService();
        $minDiscount  = $agentService->findEntryDiscount($agent, $channel);

        $discount = (string)$post['discount'];
        if (!is_numeric($discount)) {
            $this->error('折扣必须是数字');
        }
        if (bccomp($discount, '1', 2) < 0 || bccomp($discount, '10', 2) > 0) {
            $this->error('折扣范围必须在 1 到 10 之间');
        }
        if (bccomp($discount, $minDiscount, 2) < 0) {
            $this->error('设置的折扣不能低于上级代理的折扣（最低：' . $minDiscount . ' 折）');
        }

        $entryDiscount = \app\admin\model\agent\UserEntryDiscount::where('agent_id', $agent['id'])
            ->where('ec_id', $channel->id)
            ->find();
        if ($entryDiscount) {
            $entryDiscount->discount = $discount;
            $entryDiscount->save();
        } else {
            $entryDiscount = \app\admin\model\agent\UserEntryDiscount::create([
                'user_id'  => $agent['user_id'],
                'agent_id' => $agent['id'],
                'ec_id'    => $channel->id,
                'discount' => $discount,
                'status'   => 1,
            ]);
        }

        $this->success('折扣设置成功', $entryDiscount);
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: RateService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/RateService.php

namespace app\common\service;

class RateService{
    private \app\admin\model\User $user;
    private \app\admin\model\agent\Agent $agent;
    private array $actionTypes = ['agent', null];
    // [opcode 对照] 构造器两个形参均为 RECV_INIT（默认值槽位存在，值丢失），按 null 默认还原，供单参调用（api/agent/UserRate）
    public function __construct(\app\admin\model\User|null $user = null, \app\admin\model\agent\Agent|null $agent = null)
    {
        if ($user) {
            $this->user = $user;
        }
        if ($agent) {
            $this->agent = $agent;
        }
    }

    public function setrate(string|null $action_type = null, string|null $action_id = null, string|null $rate = null, int|null $channel_id = null): \app\admin\model\user\Rate
    {
        if (!in_array($action_type, $this->actionTypes)) {
            throw new \ba\Exception('操作人类型错误');
        }
        if (0 < $channel_id) {
            $channel = \app\admin\model\EntryChannel::find($channel_id);
            if (!$channel) {
                throw new \ba\Exception('通道不存在');
            }
        }
        if ($action_type == 'agent') {
            $agent = \app\admin\model\agent\Agent::find($action_id);
            if (!$agent) {
                throw new \ba\Exception('代理商不存在');
            }
            $this->user = $agent->user;
            if ($rate < $this->getuserrate($channel_id)) {
                throw new \ba\Exception('费率不能小于当前代理商的费率');
            }
        }
        $userRate = \app\admin\model\user\Rate::where('user_id', $this->user->id)
            ->where('rate_type', 'order')
            ->where('channel_id', $channel_id)
            ->find();
        if (!$userRate) {
            $userRate = \app\admin\model\user\Rate::create([
                'user_id'     => $this->user->id,
                'channel_id'  => $channel_id,
                'rate_type'   => 'order',
                'rate'        => $rate,
                'action_type' => $action_type,
                'action_id'   => $action_id,
                'status'      => 1,
                'create_time' => time(),
                'update_time' => time(),
            ]);
        }
        $userRate->rate        = $rate;
        $userRate->action_type = $action_type;
        $userRate->action_id   = $action_id;
        $userRate->save();
        return $userRate;
    }

    public function getuserrate(int|null $channel_id = null): string
    {
        if (0 < $channel_id) {
            $userRate = \app\admin\model\user\Rate::where('user_id', $this->user->id)
                ->where('channel_id', $channel_id)
                ->where('rate_type', 'order')
                ->where('status', 1)
                ->find();
            if ($userRate && 0 < $userRate->rate) {
                return $userRate->rate;
            }
            if ($this->agent) {
                $agentSubRate = \app\admin\model\agent\SubscribeEntryRate::where('subscribe_id', $this->agent->subscribe_id)
                    ->where('ec_id', $channel_id)
                    ->where('status', 1)
                    ->find();
                if ($agentSubRate && 0 < $agentSubRate->rate) {
                    return $agentSubRate->rate;
                }
                if ($this->agent->subscribe) {
                    return $this->agent->subscribe->order_rate;
                }
            }
            $userSubRate = \app\admin\model\user\SubscribeEntryRate::where('subscribe_id', $this->user->subscribe_id)
                ->where('ec_id', $channel_id)
                ->where('status', 1)
                ->find();
            if ($userSubRate && 0 < $userSubRate->rate) {
                return $userSubRate->rate;
            }
            $entryChannel = \app\admin\model\EntryChannel::find($channel_id);
            if ($entryChannel && 0 < $entryChannel->order_rate) {
                return $entryChannel->order_rate;
            }
        }
        $userRate = \app\admin\model\user\Rate::where('user_id', $this->user->id)
            ->where('rate_type', 'order')
            ->where('status', 1)
            ->where('channel_id', 0)
            ->find();
        if ($userRate && 0 < $userRate->rate) {
            return $userRate->rate;
        }
        if ($this->agent && $this->agent->subscribe) {
            return $this->agent->subscribe->order_rate;
        }
        return $this->user->subscribe->content['order_rate'];
    }

}

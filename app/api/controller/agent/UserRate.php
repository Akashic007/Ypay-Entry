<?php

// 还原自 Swoole Compiler 加密文件: UserRate.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/agent/UserRate.php

namespace app\api\controller\agent;

class UserRate extends \app\common\controller\Frontend {
    private \app\admin\model\agent\Agent $agent;
    private \app\admin\model\User $user;
    private array $topAgentMethod = [''];
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
        if (in_array($this->request->action(), $this->topAgentMethod)) {
            if ($this->agent->subscribe->is_top != 1) {
                $this->error('非顶级代理无权操作');
            }
        }

        $userId = $this->request->param('user_id');
        if (!$userId) {
            $this->error('用户ID为空');
        }
        $user = \app\admin\model\User::find($userId);
        if (!$user) {
            $this->error('用户不存在');
        }
        if ($user->agent_id != $this->agent->id) {
            $this->error('非当前代理的下级用户');
        }
        $this->user = $user;
    }

    public function getratelist(): void
    {
        $list = \app\admin\model\user\Rate::where('user_id', $this->user->id)
            ->where('status', 1)
            ->with([
                // [opcode 对照] field 数组末位元素被保护清除，按展示语义补 'nickname'
                'user' => function ($query) {
                    $query->field(['id', 'nickname']);
                },
                'channel' => function ($query) {
                    $query->with(['sp' => function ($query) {
                        $query->field(['id', 'name']);
                    }])
                    // [opcode 对照] ['id', <被清除>, 'sp_id', 'platform_code']，第 2 元素按表结构语义补 'name'
                    ->field(['id', 'name', 'sp_id', 'platform_code']);
                },
            ])
            ->select();
        $result = [];
        foreach ($list as $item) {
            $rateName = '基础费率';
            if ($item['channel']) {
                $rateName = '进件通道费率';
                $entryChannel = getEntryChannelName($item['channel']['platform_code']);
                $item['channel'] = $entryChannel['name'] . '[' . $item['channel']['sp']['name'] . ']';
            }
            $item['rate_name'] = $rateName;
            $result[] = $item;
        }
        $this->success('ok', $result);
    }

    public function getentrychannellist(): void
    {
        $entryRequest = new \app\common\service\entry\EntryRequest();
        $entryService = new \app\common\service\EntryService($entryRequest);
        // [opcode 对照] 方法名字面量真值 getLocalChannel；EntryService（含加密原件）无此方法，原码即为潜在缺陷调用
        $list = $entryService->getLocalChannel();
        $this->success('ok', $list);
    }

    public function setrate(): void
    {
        // [opcode 对照] post 数组第 2 元素被保护清除，按后文 $rate['channel_id'] 取值补 'channel_id'
        $rate = $this->request->post(['rate', 'channel_id']);
        if (empty($rate['channel_id'])) {
            $rate['channel_id'] = 0;
        }
        try {
            $rateService = new \app\common\service\RateService($this->user);
            $res = $rateService->setRate('agent', $this->agent->id, $rate['rate'], $rate['channel_id']);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('设置成功', $res);
    }

}

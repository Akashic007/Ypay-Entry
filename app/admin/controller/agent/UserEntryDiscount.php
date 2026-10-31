<?php

// 还原自 Swoole Compiler 加密文件: UserEntryDiscount.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/agent/UserEntryDiscount.php

namespace app\admin\controller\agent;

class UserEntryDiscount extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', 'create_time'];
    protected array $withJoinTable = ['user', 'agent'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\agent\UserEntryDiscount();
    }

    /**
     * 查看（原文件含 index 覆写，opcode 真值：withJoin(user,agent) +
     * visible(['user'=>['username'],'agent'=>['id','user_id']]) + with(ec: field([sp_id,platform_code,id,status,price])
     * ->with(sp: field(['name','id']))) + each: $item['ec_name'] = getEntryChannelName($item->ec->platform_code)['name']）
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->visible([
                'user'  => ['username'],
                'agent' => ['id', 'user_id'],
            ])
            ->with(['ec' => function ($query) {
                $query->field(['sp_id', 'platform_code', 'id', 'status', 'price'])
                    ->with(['sp' => function ($query) {
                        $query->field(['name', 'id']);
                    }]);
            }])
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit)
            ->each(function ($item, $key) {
                $item['ec_name'] = getEntryChannelName($item->ec->platform_code)['name'];
            });

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 通道折扣列表（opcode 真值：EntryChannel::with(agentUserDiscount/where(agent_id) + sp/field(['id','name']))
     * → withoutField(['config']) → select → each：channel_name = (name ?? getEntryChannelName(platform_code)['name'])
     * . '[' . sp.name . ']'，unset sp）
     */
    public function getlist(): void
    {
        $agent_id = $this->request->param('agent_id');
        if (empty($agent_id)) {
            $this->error('代理ID为空');
        }
        $channelList = \app\admin\model\EntryChannel::with([
            'agentUserDiscount' => function ($query) use ($agent_id) {
                $query->where('agent_id', $agent_id);
            },
            'sp' => function ($query) {
                $query->field(['id', 'name']);
            },
        ])
            ->withoutField(['config'])
            ->select()
            ->each(function ($item) {
                $item->channel_name = ($item['name'] ?? getEntryChannelName($item->platform_code)['name']) . '[' . $item['sp']['name'] . ']';
                unset($item['sp']);
            });

        $this->success('ok', $channelList);
    }

    /**
     * 设置通道折扣（opcode 真值：post(['agent_id','ec_id','discount','api_price'])；
     * Agent::find(agent_id)/EntryChannel::find(ec_id) 双校验；模型 where(agent_id)+where(ec_id)+find
     * 无行则 new UserEntryDiscount 并填 agent_id/user_id(取自 agent)/ec_id/status=1，
     * 末尾统一写 discount/api_price 后 save；success('设置成功')）
     */
    public function setdiscount(): void
    {
        $data = $this->request->post(['agent_id', 'ec_id', 'discount', 'api_price']);
        if (empty($data['agent_id'])) {
            $this->error('代理ID为空');
        }
        if (empty($data['ec_id'])) {
            $this->error('通道ID为空');
        }
        if (empty($data['discount'])) {
            $this->error('折扣为空');
        }
        $agent = \app\admin\model\agent\Agent::find($data['agent_id']);
        if (empty($agent)) {
            $this->error('代理商不存在');
        }
        $ec = \app\admin\model\EntryChannel::find($data['ec_id']);
        if (!$ec) {
            $this->error('进件通道不存在');
        }
        $discount = $this->model
            ->where('agent_id', $data['agent_id'])
            ->where('ec_id', $data['ec_id'])
            ->find();
        if (!$discount) {
            $discount = new \app\admin\model\agent\UserEntryDiscount();
            $discount->agent_id = $data['agent_id'];
            $discount->user_id = $agent->user_id;
            $discount->ec_id = $data['ec_id'];
            $discount->status = 1;
        }
        $discount->discount = $data['discount'];
        $discount->api_price = $data['api_price'];
        $discount->save();
        $this->success('设置成功');
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: Rate.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/user/Rate.php

namespace app\admin\controller\user;

class Rate extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', 'create_time', 'update_time'];
    protected array $withJoinTable = ['user', 'channel'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\user\Rate();
    }

    /**
     * 查看（原文件含 index 覆写，opcode 结构 = traits/Backend::index 变体：
     * 无 field(indexField)，改为 visible(['user'=>['username'],'channel'=>['pay_type','channel_type']])）
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
                'user'    => ['username'],
                'channel' => ['pay_type', 'channel_type'],
            ])
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 用户通道费率列表（opcode 真值：EntryChannel::with(userRate/where(user_id)+where(rate_type,'order')
     * + sp/field(['id','name'])) → withoutField(['config']) → select → each：
     * channel_name = (name ?? getEntryChannelName(platform_code)['name']) . '[' . sp.name . ']'，unset sp）
     */
    public function getlist(): void
    {
        $user_id = $this->request->param('user_id');
        if (empty($user_id)) {
            $this->error('用户ID为空');
        }
        $channelList = \app\admin\model\EntryChannel::with([
            'userRate' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id)
                    ->where('rate_type', 'order');
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
     * 设置用户通道费率（opcode 真值：post(['user_id','channel_id','rate'])；
     * EntryChannel::find(channel_id)/User::find(user_id) 双校验；
     * user\Rate where(user_id)+where(channel_id)+where(rate_type,'order') 查行，
     * 有行更新 rate/action_type='admin'/action_id=$this->auth->id 并 save，
     * 无行 new Rate() 补 user_id/channel_id/rate/rate_type='order'/action_type/action_id 后 save；
     * success('设置成功', $rate)）
     */
    public function setrate(): void
    {
        $data = $this->request->post(['user_id', 'channel_id', 'rate']);
        if (empty($data['user_id'])) {
            $this->error('用户ID为空');
        }
        if (empty($data['channel_id'])) {
            $this->error('进件通道ID为空');
        }
        if (empty($data['rate'])) {
            $this->error('费率为空');
        }
        $ec = \app\admin\model\EntryChannel::find($data['channel_id']);
        if (!$ec) {
            $this->error('通道不存在');
        }
        $user = \app\admin\model\User::find($data['user_id']);
        if (!$user) {
            $this->error('用户不存在');
        }
        $rate = $this->model
            ->where('user_id', $data['user_id'])
            ->where('channel_id', $data['channel_id'])
            ->where('rate_type', 'order')
            ->find();
        if ($rate) {
            $rate->rate = $data['rate'];
            $rate->action_type = 'admin';
            $rate->action_id = $this->auth->id;
            $rate->save();
        } else {
            $rate = new \app\admin\model\user\Rate();
            $rate->user_id = $data['user_id'];
            $rate->channel_id = $data['channel_id'];
            $rate->rate = $data['rate'];
            $rate->rate_type = 'order';
            $rate->action_type = 'admin';
            $rate->action_id = $this->auth->id;
            $rate->save();
        }
        $this->success('设置成功', $rate);
    }

}

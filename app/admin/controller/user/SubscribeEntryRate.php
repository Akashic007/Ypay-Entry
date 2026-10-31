<?php

// 还原自 Swoole Compiler 加密文件: SubscribeEntryRate.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/user/SubscribeEntryRate.php

namespace app\admin\controller\user;

class SubscribeEntryRate extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', 'create_time'];
    protected array $withJoinTable = ['subscribe', 'ec'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\user\SubscribeEntryRate();
    }

    /**
     * 查看（原文件含 index 覆写，opcode 结构 = traits/Backend::index 变体：
     * 无 field(indexField)，改为 visible(['subscribe'=>['name'],'ec'=>['platform_code','mch_id']])）
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
                'subscribe' => ['name'],
                'ec'        => ['platform_code', 'mch_id'],
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
     * 通道费率列表（opcode 真值：EntryChannel::with(userSubscribeRate/where(subscribe_id)
     * + sp/field(['id','name','logo'])) → withoutField(['config']) → select → each：
     * channel_name = (name ?? getEntryChannelName(platform_code)['name']) . '[' . sp.name . ']'，unset sp）
     */
    public function getlist(): void
    {
        $s_id = $this->request->param('s_id');
        if (empty($s_id)) {
            $this->error('会员套餐ID为空');
        }
        $channelList = \app\admin\model\EntryChannel::with([
            'userSubscribeRate' => function ($query) use ($s_id) {
                $query->where('subscribe_id', $s_id);
            },
            'sp' => function ($query) {
                $query->field(['id', 'name', 'logo']);
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
     * 设置通道费率（opcode 真值：post(['s_id','ec_id','rate'])；EntryChannel::find(ec_id)→通道不存在、
     * user\Subscribe::find(s_id)→会员套餐不存在；模型 where(subscribe_id)+where(ec_id)+find，
     * 有行更新 rate 并 save，否则 $this->model 四属性（subscribe_id/ec_id/rate/status=1）+ save；
     * success('设置成功', $rate)）
     */
    public function setrate(): void
    {
        $data = $this->request->post(['s_id', 'ec_id', 'rate']);
        if (empty($data['s_id'])) {
            $this->error('代理套餐ID为空');
        }
        if (empty($data['ec_id'])) {
            $this->error('通道ID为空');
        }
        if (empty($data['rate'])) {
            $this->error('费率为空');
        }
        $ec = \app\admin\model\EntryChannel::find($data['ec_id']);
        if (!$ec) {
            $this->error('通道不存在');
        }
        $subscribe = \app\admin\model\user\Subscribe::find($data['s_id']);
        if (!$subscribe) {
            $this->error('会员套餐不存在');
        }
        $rate = $this->model
            ->where('subscribe_id', $data['s_id'])
            ->where('ec_id', $data['ec_id'])
            ->find();
        if ($rate) {
            $rate->rate = $data['rate'];
            $rate->save();
        } else {
            $this->model->subscribe_id = $data['s_id'];
            $this->model->ec_id = $data['ec_id'];
            $this->model->rate = $data['rate'];
            $this->model->status = 1;
            $this->model->save();
        }
        $this->success('设置成功', $rate);
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: SubscribeEntryDiscount.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/agent/SubscribeEntryDiscount.php

namespace app\admin\controller\agent;

class SubscribeEntryDiscount extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', 'create_time'];
    protected array $withJoinTable = ['ec', 'subscribe'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\agent\SubscribeEntryDiscount();
    }

    /**
     * 查看（原文件含 index 覆写，opcode 结构 = traits/Backend::index 变体：
     * 无 field(indexField)，改为 visible(['ec'=>['sp_id','platform_code'],'subscribe'=>['name']])，
     * 隐藏字面量按字符串池序还原：'sp_id'→'platform_code'→'subscribe'→'name'）
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
                'ec'        => ['sp_id', 'platform_code'],
                'subscribe' => ['name'],
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
     * 通道折扣列表（opcode 真值：with agentSubscribeDiscount/where(subscribe_id) + sp/field(['id','name'])
     * → withoutField(['config']) → select → each：channel_name = (name ?? getEntryChannelName(platform_code)['name']) . '[' . sp.name . ']'，
     * 'TRMQ]TS9GZY^_^\x12CJ\x16\x18'=getEntryChannelName；隐藏字面量 'G@'='sp' 按池序复用）
     */
    public function getlist(): void
    {
        $s_id = $this->request->param('s_id');
        if (empty($s_id)) {
            $this->error('代理套餐ID为空');
        }
        $channelList = \app\admin\model\EntryChannel::with([
            'agentSubscribeDiscount' => function ($query) use ($s_id) {
                $query->where('subscribe_id', $s_id);
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
     * 设置通道折扣（opcode 真值：post(['s_id','ec_id','discount'])，arData 32 字节桶直读；
     * EntryChannel::find(ec_id)/agent\Subscribe::find(s_id) 双校验；已有行则更新 discount 并 save，
     * 否则 $this->model 四属性（subscribe_id/ec_id/discount/status=1）+ save；success('设置成功', $discount)）
     */
    public function setdiscount(): void
    {
        $data = $this->request->post(['s_id', 'ec_id', 'discount']);
        if (empty($data['s_id'])) {
            $this->error('代理套餐ID为空');
        }
        if (empty($data['ec_id'])) {
            $this->error('通道ID为空');
        }
        if (empty($data['discount'])) {
            $this->error('折扣为空');
        }
        $ec = \app\admin\model\EntryChannel::find($data['ec_id']);
        if (!$ec) {
            $this->error('通道不存在');
        }
        $subscribe = \app\admin\model\agent\Subscribe::find($data['s_id']);
        if (!$subscribe) {
            $this->error('代理套餐不存在');
        }
        $discount = $this->model
            ->where('subscribe_id', $data['s_id'])
            ->where('ec_id', $data['ec_id'])
            ->find();
        if ($discount) {
            $discount->discount = $data['discount'];
            $discount->save();
        } else {
            $this->model->subscribe_id = $data['s_id'];
            $this->model->ec_id = $data['ec_id'];
            $this->model->discount = $data['discount'];
            $this->model->status = 1;
            $this->model->save();
        }
        $this->success('设置成功', $discount);
    }

}

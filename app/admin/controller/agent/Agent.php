<?php

// 还原自 Swoole Compiler 加密文件: Agent.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/agent/Agent.php

namespace app\admin\controller\agent;

class Agent extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', 'create_time', 'update_time'];
    protected array $withJoinTable = ['user', 'subscribe'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\agent\Agent();
    }

    /**
     * 查看（原文件含 index 覆写，opcode 结构与 traits/Backend::index 完全同构：
     * select 参数→queryBuilder→indexField/withJoin/alias/where/order/paginate→success(list/total/remark)）
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
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
     * 重置 api_id/api_key/invite_code（opcode 真值：'C\JRDaQ)mV'=resetApiId、'@TJSCaQ)oWA'=resetApiKey、
     * '@\JPAiO6MF]s^_'=resetInviteCode；try_catch 元素表 try_op=20(catch_op=49) 仅包裹 switch；
     * 成功后 $agent->refresh() 并返回 ['type' => $agent->{type}]）
     */
    public function reset(): void
    {
        $id   = $this->request->param('id');
        $type = $this->request->param('type');
        if (empty($type)) {
            $this->error('重置类型为空');
        }
        $agent = $this->model->find($id);

        try {
            switch ($type) {
                case 'api_id':
                    $agent->resetApiId($agent->id);
                    break;
                case 'api_key':
                    $agent->resetApiKey($agent->id);
                    break;
                case 'invite_code':
                    $agent->resetInviteCode($agent->id);
                    break;
                default:
                    throw new \ba\Exception('重置类型错误');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $agent->refresh();
        $this->success('重置成功', [$type => $agent->{$type}]);
    }

}

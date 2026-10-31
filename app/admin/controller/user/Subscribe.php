<?php

// 还原自 Swoole Compiler 加密文件: Subscribe.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/user/Subscribe.php

namespace app\admin\controller\user;

class Subscribe extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $defaultSortField = 'weigh,desc';
    protected string|array $preExcludeFields = ['id', 'create_time', 'update_time'];
    protected string|array $quickSearchField = ['id'];
    protected array $withJoinTable = ['subscribeGroup'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\user\Subscribe();
    }

    /**
     * 查看（原文件含 index 覆写，opcode 结构 = traits/Backend::index 变体：
     * 无 field(indexField)，改为 visible(['subscribeGroup'=>['name']])）
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
                'subscribeGroup' => ['name'],
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
     * 删除（opcode 真值：ids/a 批量删除；逐行校验 User::where(subscribe_id, $row->id)->find()
     * 命中则 throw new \ba\Exception('套餐[' . $row->name . ']存在开通的用户，无法删除')；
     * 全部通过后 $row->delete() 并计数，commit/catch(rollback+error)；
     * $count 真 → success(__('Deleted successfully'))，否则 error(__('No rows were deleted'))）
     */
    public function del(): void
    {
        $where = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }
        $ids = $this->request->param('ids/a', []);
        $pk  = $this->model->getPk();
        $where[] = [$pk, 'in', $ids];
        $list  = $this->model->where($where)->select();
        $count = 0;
        $this->model->startTrans();
        try {
            foreach ($list as $row) {
                $user = \app\admin\model\User::where('subscribe_id', $row->id)->find();
                if ($user) {
                    throw new \ba\Exception('套餐[' . $row->name . ']存在开通的用户，无法删除');
                }
                $row->delete();
                $count++;
            }
            $this->model->commit();
        } catch (\Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }

}

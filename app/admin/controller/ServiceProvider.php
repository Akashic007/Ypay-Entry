<?php

// 还原自 Swoole Compiler 加密文件: ServiceProvider.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/ServiceProvider.php

namespace app\admin\controller;

class ServiceProvider extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', null, 'create_time'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\ServiceProvider();
    }

    public function del(): void
    {
        $where = [];
        $adminIds = $this->getDataLimitAdminIds();
        if ($adminIds) {
            $where[] = [$this->dataLimitField, 'in', $adminIds];
        }
        $ids = $this->request->param('ids/a', []);
        $where[] = [$this->model->getPk(), 'in', $ids];
        $list = $this->model->where($where)->select();
        $count = 0;
        $this->model->startTrans();
        try {
            foreach ($list as $item) {
                \app\admin\model\EntryChannel::where('sp_id', $item->id)->delete();
                $count += $item->delete();
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

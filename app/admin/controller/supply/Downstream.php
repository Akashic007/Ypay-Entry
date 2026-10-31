<?php

// 还原自 Swoole Compiler 加密文件: Downstream.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/supply/Downstream.php

namespace app\admin\controller\supply;

class Downstream extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', null, 'create_time'];
    protected array $withJoinTable = ['agent'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\supply\Downstream();
    }

    public function audit(): void
    {
        $id     = $this->request->param('id');
        $reject = $this->request->param('reject');
        $type   = $this->request->param('type');
        $down   = $this->model->find($id);

        \think\facade\Db::startTrans();
        try {
            if ($type == 'reject') {
                $down->status   = 2;
                $down->rejected = $reject ?? '';
            } else {
                $down->status = 1;
            }
            $down->save();

            $service = new \app\common\service\SupplyService();
            $res     = $service->notifyDownDocking($down);
            if ($res['code'] != 1) {
                throw new \ba\Exception($res['msg']);
            }
            \think\facade\Db::commit();
        } catch (\ba\Exception $e) {
            \think\facade\Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('操作成功', $res);
    }

}

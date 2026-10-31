<?php

// 还原自 Swoole Compiler 加密文件: Upstream.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/supply/Upstream.php

namespace app\admin\controller\supply;

class Upstream extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id'];
    protected string|array $quickSearchField = ['id'];
    protected array $noNeedLogin = ['dockingCallback'];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\supply\Upstream();
    }

    public function add(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式');
        }
        $post = $this->request->post();

        \think\facade\Db::startTrans();
        try {
            $upstream = \app\admin\model\supply\Upstream::create($post);
            $service  = new \app\common\service\SupplyService();
            $res      = $service->applyUpstreamDocking($upstream);
            if ($res['code'] != 1) {
                throw new \Exception('申请失败：' . $res['msg']);
            }
            if ($res['data']['status'] == 1) {
                $upstream->api_status = 1;
                $upstream->save();
            }
            \think\facade\Db::commit();
        } catch (\Throwable $e) {
            \think\facade\Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('新增成功', $res);
    }

    public function dockingcallback(): void
    {
        $params = $this->request->param();
        if (empty($params['status'])) {
            $this->error('状态为空');
        }
        if (empty($params['sign'])) {
            $this->error('签名为空');
        }
        if (empty($params['api_id'])) {
            $this->error('API ID为空');
        }
        $up = $this->model->where('api_id', $params['api_id'])->find();
        if (empty($up)) {
            $this->error('上游对接记录不存在');
        }
        if (!\app\common\library\SignUtil::verifySign($params, $up->api_key)) {
            $this->error('签名验证错误');
        }
        $up->api_status = $params['status'];
        if (!empty($params['reject_msg'])) {
            $up->rejected = $params['reject_msg'];
        }
        $up->save();
        $this->success('success');
    }

    public function querydockingstatus(): void
    {
        $id = $this->request->param('id');
        $up = $this->model->where('id', $id)->find();
        $service = new \app\common\service\SupplyService();
        try {
            $res = $service->queryUpstreamDockingStatus($up);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        if ($res['code'] != '1') {
            $this->error($res['msg']);
        }
        if (!isset($res['data']['status'])) {
            $up->api_status = 0;
            $up->save();
            $this->error('状态异常，请确认对接是否成功');
        }
        $up->api_status = $res['data']['status'];
        if (!empty($res['data']['rejected'])) {
            $up->rejected = $res['data']['rejected'];
        }
        $up->save();
        if ($res['data']['status'] != 1) {
            $this->error('已驳回：' . $res['data']['rejected']);
        }
        $this->success('审核通过', $up);
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: Config.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/oauth/Config.php

namespace app\admin\controller\oauth;

class Config extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $defaultSortField = 'weigh,desc';
    protected string|array $preExcludeFields = ['id'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\oauth\Config();
    }

    public function getlist(): void
    {
        $this->success('ok123', $this->model->getList());
    }


    public function edit(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请使用正确的请求方式');
        }
        $data   = $this->request->post();
        $update = [];
        foreach ($data as $id => $item) {
            $update[$id] = [
                'id'    => $item['id'],
                'value' => $item['value'],
            ];
        }
        $this->model->saveAll($update);
        $this->success('保存成功');
    }

    public function setmethod(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求错误');
        }
        $data = $this->request->post(['method', 'group', 'title']);
        if (empty($data['method']) || empty($data['group'])) {
            $this->error('参数错误');
        }
        $id = $this->model->where('group', $data['group'])->value('id');
        if (empty($id)) {
            $this->error('该配置组不存在');
        }
        $row = $this->model->where(['group' => 'config', 'name' => 'login_method'])->find();
        $row['config'][$data['method']] = [
            'group' => $data['group'],
            'title' => $data['title'],
        ];
        $row->save(['config' => $row['config']]);
        $this->success('ok', $row['config']);
    }

    public function unmethod(): void
    {
        $method = $this->request->param('method');
        if (empty($method)) {
            $this->error('方法为空');
        }
        $row = $this->model->where(['group' => 'config', 'name' => 'login_method'])->find();
        unset($row['config'][$method]);
        $row->save(['config' => $row['config']]);
        $this->success('设置成功');
    }
}

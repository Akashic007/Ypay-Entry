<?php

// 还原自 Swoole Compiler 加密文件: Cloud.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/Cloud.php

namespace app\admin\controller;

class Cloud extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $defaultSortField = 'weigh,desc';
    protected string|array $preExcludeFields = ['id'];
    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Cloud();
    }

    public function getCloudTypeByPlatform(): void
    {
        $platform = $this->request->param('platform');
        if (empty($platform)) {
            $this->error('平台标识为空');
        }

        $cloudTypes = config('channel.' . $platform . '_cloud');
        if (empty($cloudTypes)) {
            $this->error('没有可用的云端类型');
        }

        $this->success('ok', $cloudTypes);
    }

}

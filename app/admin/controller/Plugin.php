<?php

// 还原自 Swoole Compiler 加密文件: Plugin.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/Plugin.php

namespace app\admin\controller;

class Plugin extends \app\common\controller\Backend {
    protected object $model;
    protected array $noNeedPermission = ['index'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Plugin();
    }

    public function index(): void
    {
        $page    = $this->request->get('page/d', 1);
        $limit   = $this->request->get('limit/d', 20);
        $service = new \app\common\service\PluginService();
        $result  = $service->getList([], $page, $limit);
        $this->success('', [
            'list'   => $result['list'],
            'total'  => $result['total'],
            'remark' => get_route_remark(),
        ]);
    }

    public function install(): void
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error('请上传插件安装包');
        }
        $service = new \app\common\service\PluginService();
        try {
            $result = $service->install($file, $this->auth->id);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('插件安装成功', $result);
    }

    public function uploadupdate(): void
    {
        $id = $this->request->param('id/d');
        if (empty($id)) {
            $this->error('插件ID不能为空');
        }
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error('请上传插件更新包');
        }
        $service = new \app\common\service\PluginService();
        try {
            $result = $service->uploadUpdate($file, $id, $this->auth->id);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('插件更新成功', $result);
    }

    public function onlineupdate(): void
    {
        $id = $this->request->param('id/d');
        if (empty($id)) {
            $this->error('插件ID不能为空');
        }
        $service = new \app\common\service\PluginService();
        try {
            $result = $service->onlineUpdate($id);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('插件更新成功', $result);
    }

    public function checkupdate(): void
    {
        $id = $this->request->param('id/d');
        if (empty($id)) {
            $this->error('插件ID不能为空');
        }
        $service = new \app\common\service\PluginService();
        try {
            $result = $service->checkUpdate($id);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('OK', $result);
    }

    public function uninstall(): void
    {
        $id = $this->request->param('id/d');
        if (empty($id)) {
            $this->error('插件ID不能为空');
        }
        $service = new \app\common\service\PluginService();
        try {
            $service->uninstall($id);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('插件卸载成功');
    }

    public function activeauth(): void
    {
        $id      = $this->request->param('id/d');
        $authKey = $this->request->param('auth_key/s', '');
        if (empty($id)) {
            $this->error('插件ID不能为空');
        }
        $service = new \app\common\service\PluginService();
        try {
            $result = $service->activeAuth($id, $authKey);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('授权成功', $result);
    }

    public function getauthstatus(): void
    {
        $id = $this->request->param('id/d');
        if (empty($id)) {
            $this->error('插件ID不能为空');
        }
        $service = new \app\common\service\PluginService();
        try {
            $result = $service->getAuthStatus($id);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('', $result);
    }

    public function cleanauth(): void
    {
        $id = $this->request->param('id/d');
        if (empty($id)) {
            $this->error('插件ID不能为空');
        }
        $service = new \app\common\service\PluginService();
        try {
            $service->cleanAuth($id);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('授权已清除');
    }

    public function togglestatus(): void
    {
        $id = $this->request->param('id/d');
        if (empty($id)) {
            $this->error('插件ID不能为空');
        }
        $service = new \app\common\service\PluginService();
        try {
            $result = $service->toggleStatus($id);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('操作成功', $result);
    }

}

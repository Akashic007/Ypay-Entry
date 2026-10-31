<?php

// 还原自 Swoole Compiler 加密文件: Module.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/Module.php

namespace app\admin\controller;

class Module extends \app\common\controller\Backend {
    protected array $noNeedPermission = ['state'];
    public function initialize(): void
    {
        parent::initialize();
    }

    // 补还原：加密原件 function_table 含 index（原文件降级时整方法丢失，opcode 全量存在）
    public function index(): void
    {
        $this->success('', [
            'sysVersion'  => \think\facade\Config::get('buildadmin.version'),
            'installed'   => \app\admin\library\module\Server::installedList(root_path() . 'modules' . DIRECTORY_SEPARATOR),
            'nuxtVersion' => \app\admin\library\module\Server::getNuxtVersion(),
        ]);
    }
    public function state(): void
    {
        $uid = $this->request->get('uid/s', '');
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        $this->success('', [
            'state' => \app\admin\library\module\Manage::instance($uid)->getInstallState(),
        ]);
    }

    public function install(): void
    {
        \app\admin\model\AdminLog::instance()->setTitle(__('Install module'));
        $uid     = $this->request->get('uid/s', '');
        $token   = $this->request->get('token/s', '');
        $orderId = $this->request->get('orderId/d', 0);
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        $res = [];
        try {
            $res = \app\admin\library\module\Manage::instance($uid)->install($token, $orderId);
        } catch (\ba\Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (\Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success('', [
            'data' => $res,
        ]);
    }

    public function dependentinstallcomplete(): void
    {
        $uid = $this->request->get('uid/s', '');
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        try {
            \app\admin\library\module\Manage::instance($uid)->dependentInstallComplete('all');
        } catch (\ba\Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (\Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function changestate(): void
    {
        \app\admin\model\AdminLog::instance()->setTitle(__('Change module state'));
        $uid   = $this->request->post('uid/s', '');
        $state = $this->request->post('state/b', false);
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        $info = [];
        try {
            $info = \app\admin\library\module\Manage::instance($uid)->changeState($state);
        } catch (\ba\Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (\Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success('', [
            'info' => $info,
        ]);
    }

    public function uninstall(): void
    {
        \app\admin\model\AdminLog::instance()->setTitle(__('Unload module'));
        $uid = $this->request->get('uid/s', '');
        if (!$uid) {
            $this->error(__('Parameter error'));
        }
        try {
            \app\admin\library\module\Manage::instance($uid)->uninstall();
        } catch (\ba\Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (\Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function update(): void
    {
        \app\admin\model\AdminLog::instance()->setTitle(__('Update module'));
        $uid     = $this->request->get('uid/s', '');
        $token   = $this->request->get('token/s', '');
        $orderId = $this->request->get('orderId/d', 0);
        if (!$token || !$uid) {
            $this->error(__('Parameter error'));
        }
        try {
            \app\admin\library\module\Manage::instance($uid)->update($token, $orderId);
        } catch (\ba\Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (\Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success();
    }

    public function upload(): void
    {
        \app\admin\model\AdminLog::instance()->setTitle(__('Upload install module'));
        $file  = $this->request->get('file/s', '');
        $token = $this->request->get('token/s', '');
        if (!$file) {
            $this->error(__('Parameter error'));
        }
        if (!$token) {
            $this->error(__('Please login to the official website account first'));
        }
        $info = [];
        try {
            $info = \app\admin\library\module\Manage::instance()->upload($token, $file);
        } catch (\ba\Exception $e) {
            $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (\Throwable $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success('', [
            'info' => $info,
        ]);
    }

}

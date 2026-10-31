<?php

// 还原自 Swoole Compiler 加密文件: YAuth.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/YAuth.php

namespace app\admin\controller;

class YAuth extends \app\common\controller\Backend {
    public function initialize(): void
    {
        parent::initialize();
    }

    public function activeauth(): void
    {
        if ($this->request->isPost()) {
            $authKey = $this->request->post('auth_key');
            $type    = $this->request->post('type');
            $client  = new \app\common\library\YAuthClient();
            if ($type == 'auth_key') {
                if (empty($authKey)) {
                    $this->error('授权密钥不能为空');
                }
                $result = $client->activeAuth($authKey);
            } else {
                $result = $client->activeAuth();
            }
            if ($result) {
                $this->success('授权成功');
            } else {
                $this->error($client->getErrorMsg());
            }
        } else {
            $this->error('错误的请求方式！');
        }
    }

    public function getauthinfo(): void
    {
        $client = new \app\common\library\YAuthClient();
        $this->success('', $client->getAuthInfo());
    }

    public function getauthstatus(): void
    {
        $client = new \app\common\library\YAuthClient();
        $this->success('ok', ['status' => $client->getAuthStatus() ? 1 : 0]);
    }

    public function cleanauthinfo(): void
    {
        $client = new \app\common\library\YAuthClient();
        $client->cleanAuth();
        $this->success('操作成功！');
    }

    public function getversion(): void
    {
        $currentVersion = \app\admin\model\Config::where('name', 'version')->value('value');
        $client         = new \app\common\library\YAuthClient();
        $versionList    = $client->getVersionList();
        $latestVersion  = $versionList['latest_version'] ? $versionList['latest_version']['version'] : $currentVersion;
        $this->success('ok', [
            'current_version' => $currentVersion,
            'version_list'    => $versionList['version_list'],
            'latest_version'  => $latestVersion,
            'is_latest'       => 0 <= $client->compareVersions($currentVersion, $latestVersion),
        ]);
    }

    public function onlineupdatenewversion(): void
    {
        $client = new \app\common\library\YAuthClient();
        $result = $client->onlineUpdateToNewVersion();
        if ($result) {
            $this->success('更新成功');
        } else {
            $this->error($client->getErrorMsg());
        }
    }

    public function uploadzipupdate(): void
    {
        \app\admin\model\AdminLog::instance()->setTitle(__('upload'));
        if (cache('system_updating')) {
            $this->error('系统正在更新中，请勿重复操作');
        }
        cache('system_updating', 1);
        try {
            $file       = $this->request->file('file');
            $upload     = new \app\common\library\Upload($file);
            $attachment = $upload->upload(null, $this->auth->id);
            if (empty($attachment['url'])) {
                throw new \RuntimeException('上传失败');
            }
            $rootPath = root_path();
            $zipPath  = $rootPath . 'public' . $attachment['url'];
            if (!is_file($zipPath)) {
                throw new \RuntimeException('更新包不存在');
            }
            $updateDir = $rootPath . 'runtime/update/update/';
            if (is_dir($updateDir)) {
                // [opcode 对照] 原件此处调用全局函数 deldir($updateDir) 删除旧更新目录；
                // 当前代码库（含还原版 common.php）未定义全局 deldir()，
                // 保守处理：优先调用 deldir()，缺失时内联等价的递归删除。
                if (function_exists('deldir')) {
                    deldir($updateDir);
                } else {
                    $items = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($updateDir, \FilesystemIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($items as $item) {
                        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
                    }
                    rmdir($updateDir);
                }
            }
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('无法打开更新包');
            }
            $zip->extractTo($updateDir);
            $zip->close();
            $handle        = opendir($updateDir);
            $hasMigrations = false;
            while (false !== ($file = readdir($handle))) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                if (is_link($updateDir . $file)) {
                    continue;
                }
                if ($file === 'database' && is_dir($updateDir . 'database/migrations')) {
                    $hasMigrations = true;
                }
                $source      = $updateDir . $file;
                $destination = $rootPath . $file;
                if (is_dir($source)) {
                    copyDirectory($source, $destination);
                } else {
                    $dir = dirname($destination);
                    if (!is_dir($dir) && !mkdir($dir, 493, true) && !is_dir($dir)) {
                        throw new \RuntimeException('目录创建失败: ' . $dir);
                    }
                    if (!copy($source, $destination)) {
                        throw new \RuntimeException('文件复制失败: ' . $file);
                    }
                    $perms = fileperms($source);
                    if ($perms !== false) {
                        chmod($destination, $perms & 511);
                    }
                }
            }
            closedir($handle);
            if ($hasMigrations) {
                \think\facade\Console::call('migrate:run');
            }
        } catch (\Throwable $e) {
            cache('system_updating', null);
            $this->error($e->getMessage());
        }
        cache('system_updating', null);
        $this->success('更新成功');
    }

}

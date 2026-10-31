<?php

// 还原自 Swoole Compiler 加密文件: PluginService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/PluginService.php

namespace app\common\service;

class PluginService{
    const SOURCE_DIR = 'source';
    const CONFIG_DIR = 'app/common/library/plugin/config/';
    const CONFIG_FILE = 'config.php';
    public function getlist(array|null $where = null, int|null $page = null, int|null $limit = null): array
    {
        $query = \app\admin\model\Plugin::where($where);
        $total = $query->count();
        $list = $query->page($page, $limit)->order('id', 'desc')->select()->toArray();
        return ['list' => $list, 'total' => $total];
    }

    public function install(\think\file\UploadedFile|null $file = null, int|null $adminId = null): array
    {
        $rootPath = \think\facade\App::getRootPath();
        $tempDir = $rootPath . 'runtime/plugin_temp_' . uniqid() . '/';
        $extractDir = $tempDir . 'extract/';
        try {
            $this->ensureDir($tempDir);
            $this->ensureDir($extractDir);
            $zipPath = $tempDir . 'plugin.zip';
            $file->move($tempDir, 'plugin.zip');
            if (!is_file($zipPath)) {
                throw new \RuntimeException('上传文件保存失败');
            }
            $this->extractZip($zipPath, $extractDir);
            $info = $this->readPluginInfo($extractDir);
            $this->validatePluginInfo($info);
            $existing = \app\admin\model\Plugin::where('code', $info['code'])->find();
            if ($existing) {
                throw new \RuntimeException('插件已存在，如需更新请使用更新功能');
            }
            $configPath = $extractDir . self::CONFIG_FILE;
            if (!is_file($configPath)) {
                throw new \RuntimeException('插件包缺少配置文件 ' . self::CONFIG_FILE);
            }
            $configDir = $rootPath . self::CONFIG_DIR;
            $this->ensureDir($configDir);
            $configFile = $configDir . $info['code'] . '.php';
            copy($configPath, $configFile);
            $this->loadPluginConfig($info['code']);
            $sourceDir = $extractDir . self::SOURCE_DIR . '/';
            if (!is_dir($sourceDir)) {
                throw new \RuntimeException('插件包缺少 ' . self::SOURCE_DIR . ' 代码目录');
            }
            $files = $this->scanRelativePaths($sourceDir);
            copyDirectory($sourceDir, $rootPath);
            if (is_dir($extractDir . 'database/migrations')) {
                $migrationFiles = $this->scanRelativePaths($extractDir . 'database/', 'database/');
                $files = array_merge($files, $migrationFiles);
                copyDirectory($extractDir . 'database/', $rootPath . 'database/');
                \think\facade\Console::call('migrate:run');
            }
            $files[] = self::CONFIG_DIR . $info['code'] . '.php';
            $plugin = new \app\admin\model\Plugin();
            $plugin->name = $info['name'];
            $plugin->code = $info['code'];
            $plugin->title = $info['title'] ?? $info['name'];
            $plugin->version = $info['version'];
            $plugin->latest_version = $info['version'];
            $plugin->status = 1;
            $plugin->install_status = 1;
            $plugin->auth_status = 0;
            $plugin->config = $info['config'] ?? [];
            $plugin->auth_info = [];
            $plugin->path_info = $files;
            $plugin->install_time = time();
            $plugin->save();
            return $plugin->toArray();
        } finally {
            $this->cleanTemp($tempDir);
        }
    }

    public function uploadupdate(\think\file\UploadedFile|null $file = null, int|null $pluginId = null, int|null $adminId = null): array
    {
        $plugin = \app\admin\model\Plugin::find($pluginId);
        if (!$plugin) {
            throw new \RuntimeException('插件不存在');
        }
        if ($plugin->install_status != 1) {
            throw new \RuntimeException('插件未安装');
        }
        $rootPath = \think\facade\App::getRootPath();
        $tempDir = $rootPath . 'runtime/plugin_temp_' . uniqid() . '/';
        $extractDir = $tempDir . 'extract/';
        try {
            $this->ensureDir($tempDir);
            $this->ensureDir($extractDir);
            $zipPath = $tempDir . 'plugin.zip';
            $file->move($tempDir, 'plugin.zip');
            if (!is_file($zipPath)) {
                throw new \RuntimeException('上传文件保存失败');
            }
            $this->extractZip($zipPath, $extractDir);
            $info = $this->readPluginInfo($extractDir);
            $this->validatePluginInfo($info);
            if ($info['code'] !== $plugin->code) {
                throw new \RuntimeException('更新包与当前插件不匹配');
            }
            $configPath = $extractDir . self::CONFIG_FILE;
            if (is_file($configPath)) {
                $configDir = $rootPath . self::CONFIG_DIR;
                $this->ensureDir($configDir);
                copy($configPath, $configDir . $plugin->code . '.php');
            }
            $sourceDir = $extractDir . self::SOURCE_DIR . '/';
            if (!is_dir($sourceDir)) {
                throw new \RuntimeException('更新包缺少 ' . self::SOURCE_DIR . ' 代码目录');
            }
            $files = $this->scanRelativePaths($sourceDir);
            copyDirectory($sourceDir, $rootPath);
            if (is_dir($extractDir . 'database/migrations')) {
                $migrationFiles = $this->scanRelativePaths($extractDir . 'database/', 'database/');
                $files = array_merge($files, $migrationFiles);
                copyDirectory($extractDir . 'database/', $rootPath . 'database/');
                \think\facade\Console::call('migrate:run');
            }
            $pathInfo = $plugin->path_info ?: [];
            $allPaths = array_values(array_unique(array_merge($pathInfo, $files)));
            $plugin->version = $info['version'];
            $plugin->latest_version = $info['version'];
            $plugin->title = $info['title'] ?? $plugin->title;
            $plugin->name = $info['name'] ?? $plugin->name;
            $plugin->path_info = $allPaths;
            if (!empty($info['config'])) {
                $plugin->config = array_merge($plugin->config, $info['config']);
            }
            $plugin->save();
            return $plugin->toArray();
        } finally {
            $this->cleanTemp($tempDir);
        }
    }

    public function onlineupdate(int|null $pluginId = null): array
    {
        $plugin = \app\admin\model\Plugin::find($pluginId);
        if (!$plugin) {
            throw new \RuntimeException('插件不存在');
        }
        if ($plugin->install_status != 1) {
            throw new \RuntimeException('插件未安装');
        }
        $client = $this->createAuthClient($plugin);
        $versionList = $client->getVersionList();
        if (empty($versionList) || empty($versionList['latest_version']['version'])) {
            throw new \RuntimeException('获取最新版本失败');
        }
        $latestVersion = $versionList['latest_version'];
        if (\app\common\library\PluginAuthClient::compareVersions($plugin->version, $latestVersion['version']) >= 0) {
            throw new \RuntimeException('当前已是最新版本');
        }
        $rootPath = \think\facade\App::getRootPath();
        $tempDir = $rootPath . 'runtime/plugin_temp_' . uniqid() . '/';
        $extractDir = $tempDir . 'extract/';
        try {
            $this->ensureDir($tempDir);
            $this->ensureDir($extractDir);
            $zipPath = $client->downloadUpdate($latestVersion['version_id'], $tempDir);
            if (!$zipPath || !file_exists($tempDir . $zipPath)) {
                throw new \RuntimeException('更新包下载失败');
            }
            $this->extractZip($tempDir . $zipPath, $extractDir);
            $configPath = $extractDir . self::CONFIG_FILE;
            if (is_file($configPath)) {
                $configDir = $rootPath . self::CONFIG_DIR;
                $this->ensureDir($configDir);
                copy($configPath, $configDir . $plugin->code . '.php');
            }
            $sourceDir = $extractDir . self::SOURCE_DIR . '/';
            $files = [];
            if (is_dir($sourceDir)) {
                $files = $this->scanRelativePaths($sourceDir);
                copyDirectory($sourceDir, $rootPath);
            }
            if (is_dir($extractDir . 'database/migrations')) {
                $migrationFiles = $this->scanRelativePaths($extractDir . 'database/', 'database/');
                $files = array_merge($files, $migrationFiles);
                copyDirectory($extractDir . 'database/', $rootPath . 'database/');
                \think\facade\Console::call('migrate:run');
            }
            $pathInfo = $plugin->path_info ?: [];
            $files = array_values(array_unique(array_merge($pathInfo, $files)));
            $info = $this->readPluginInfo($extractDir);
            $plugin->version = $latestVersion['version'];
            $plugin->latest_version = $latestVersion['version'];
            $plugin->path_info = $files;
            if (!empty($info['name'])) {
                $plugin->name = $info['name'];
            }
            if (!empty($info['title'])) {
                $plugin->title = $info['title'];
            }
            $plugin->save();
            return $plugin->toArray();
        } finally {
            $this->cleanTemp($tempDir);
        }
    }

    public function checkupdate(int|null $pluginId = null): array
    {
        $plugin = \app\admin\model\Plugin::find($pluginId);
        if (!$plugin) {
            throw new \RuntimeException('插件不存在');
        }
        $client = $this->createAuthClient($plugin);
        $versionList = $client->getVersionList();
        $latestVersion = $versionList['latest_version']['version'] ?? $plugin->version;
        $hasUpdate = \app\common\library\PluginAuthClient::compareVersions($plugin->version, $latestVersion) < 0;
        if ($hasUpdate) {
            $plugin->latest_version = $latestVersion;
            $plugin->save();
        }
        return [
            'current_version' => $plugin->version,
            'latest_version' => $latestVersion,
            'has_update' => $hasUpdate,
            'version_list' => $versionList['version_list'] ?? [],
        ];
    }

    public function uninstall(int|null $pluginId = null): bool
    {
        $plugin = \app\admin\model\Plugin::find($pluginId);
        if (!$plugin) {
            throw new \RuntimeException('插件不存在');
        }
        try {
            $client = $this->createAuthClient($plugin);
            $client->cleanAuth();
        } catch (\Throwable $e) {
        }
        $rootPath = \think\facade\App::getRootPath();
        $pathInfo = $plugin->path_info ?: [];
        $dirs = [];
        foreach ($pathInfo as $p) {
            $file = $rootPath . $p;
            if (is_file($file)) {
                @unlink($file);
            }
            $dirs[] = dirname($file);
        }
        $dirs = array_unique($dirs);
        usort($dirs, function ($a, $b) {
            return strlen($b) - strlen($a);
        });
        foreach ($dirs as $dir) {
            if (is_dir($dir) && $this->isDirEmpty($dir) && realpath($dir) !== realpath($rootPath)) {
                @rmdir($dir);
            }
        }
        $plugin->delete();
        return true;
    }

    public function activeauth(int|null $pluginId = null, string|null $authKey = null): array
    {
        $plugin = \app\admin\model\Plugin::find($pluginId);
        if (!$plugin) {
            throw new \RuntimeException('插件不存在');
        }
        $client = $this->createAuthClient($plugin, $authKey);
        $result = $client->activeAuth($authKey);
        if (!$result) {
            throw new \RuntimeException($client->getErrorMsg());
        }
        $plugin->auth_status = 1;
        $authInfo = $plugin->auth_info ?: [];
        $authInfo['auth_key'] = $authKey;
        $authInfo['auth_time'] = date('Y-m-d H:i:s');
        $plugin->auth_info = $authInfo;
        $plugin->save();
        return array_merge($plugin->toArray(), ['remote_auth_info' => $client->getAuthInfo()]);
    }

    public function getauthstatus(int|null $pluginId = null): array
    {
        $plugin = \app\admin\model\Plugin::find($pluginId);
        if (!$plugin) {
            throw new \RuntimeException('插件不存在');
        }
        $authInfo = $plugin->auth_info ?: [];
        $client = $this->createAuthClient($plugin, $authInfo['auth_key'] ?? '');
        $remoteStatus = $client->getAuthStatus();
        if ($plugin->auth_status != ($remoteStatus ? 1 : 0)) {
            $plugin->auth_status = $remoteStatus ? 1 : 0;
            $plugin->save();
        }
        return ['auth_status' => $remoteStatus ? 1 : 0, 'auth_info' => $client->getAuthInfo()];
    }

    public function cleanauth(int|null $pluginId = null): bool
    {
        $plugin = \app\admin\model\Plugin::find($pluginId);
        if (!$plugin) {
            throw new \RuntimeException('插件不存在');
        }
        $client = $this->createAuthClient($plugin);
        $client->cleanAuth();
        $plugin->auth_status = 0;
        $info = $plugin->auth_info ?: [];
        $info['auth_key'] = '';
        $info['auth_time'] = '';
        $plugin->auth_info = $info;
        $plugin->save();
        return true;
    }

    public function togglestatus(int|null $pluginId = null): array
    {
        $plugin = \app\admin\model\Plugin::find($pluginId);
        if (!$plugin) {
            throw new \RuntimeException('插件不存在');
        }
        $plugin->status = $plugin->status == 1 ? 0 : 1;
        $plugin->save();
        return $plugin->toArray();
    }

    public static function loadpluginconfig(string|null $code = null): \app\common\library\plugin\PluginConfigInterface
    {
        $configPath = \think\facade\App::getRootPath() . self::CONFIG_DIR . $code . '.php';
        if (!is_file($configPath)) {
            throw new \RuntimeException('插件配置文件不存在: ' . $code);
        }
        $instance = include $configPath;
        if (!$instance instanceof \app\common\library\plugin\PluginConfigInterface) {
            throw new \RuntimeException('插件配置文件格式错误，必须返回 PluginConfigInterface 实例: ' . $code);
        }
        return $instance;
    }

    private function createauthclient(\app\admin\model\Plugin|null $plugin = null, string|null $authKey = null): \app\common\library\PluginAuthClient
    {
        return $this->createAuthClientByCode($plugin->code, $authKey);
    }

    public static function createauthclientbycode(string|null $code = null, string|null $authKey = null): \app\common\library\PluginAuthClient
    {
        $config = self::loadPluginConfig($code);
        return new \app\common\library\PluginAuthClient($config->getAppCode(), $authKey, $config->getApiUrl());
    }

    private function scanrelativepaths(string|null $dir = null, string|null $prefix = null): array
    {
        $paths = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $relative = $prefix . ltrim(str_replace([$dir, '\\'], ['', '/'], $file->getPathname()), '/');
            $paths[] = $relative;
        }
        return $paths;
    }

    private function extractzip(string|null $zipPath = null, string|null $extractDir = null): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('无法打开压缩包');
        }
        if (!$zip->extractTo($extractDir)) {
            $zip->close();
            throw new \RuntimeException('解压失败');
        }
        $zip->close();
    }

    private function readplugininfo(string|null $dir = null): array
    {
        $infoFile = $dir . 'plugin.json';
        if (!is_file($infoFile)) {
            throw new \RuntimeException('插件包缺少 plugin.json 描述文件');
        }
        $info = json_decode(file_get_contents($infoFile), true);
        if (!$info) {
            throw new \RuntimeException('plugin.json 格式错误');
        }
        return $info;
    }

    private function validateplugininfo(array|null $info = null): void
    {
        foreach (['code', 'name'] as $field) {
            if (empty($info[$field])) {
                throw new \RuntimeException('plugin.json 缺少必要字段: ' . $field);
            }
        }
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]+$/', $info['code'])) {
            throw new \RuntimeException('插件标识码(code)格式不正确，只允许字母、数字、下划线和短横线');
        }
    }

    private function ensuredir(string|null $dir = null): void
    {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException('创建目录失败: ' . $dir);
            }
        }
    }

    private function cleantemp(string|null $tempDir = null): void
    {
        if (is_dir($tempDir)) {
            delDir($tempDir);
        }
    }

    private function isdirempty(string|null $dir = null): bool
    {
        $handle = opendir($dir);
        if (!$handle) {
            return false;
        }
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }

}

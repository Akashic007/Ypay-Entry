<?php

// 还原自 Swoole Compiler 加密文件: YAuthClient.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/YAuthClient.php

namespace app\common\library;

class YAuthClient{
    private $api = 'https://www.yfxw.cn/';
    private static $app_code;
    private $authUrl;
    private $error_msg = '未知错误';
    private $ip;
    private $domain;
    private $auth_key;
    public function __construct()
    {
        $this->auth_key = \think\facade\Cache::get('auth_key') ?? '';
        $this->ip = \think\facade\Request::server('SERVER_ADDR');
        $this->domain = \think\facade\Request::host(true);
        $this->authUrl = $this->api . 'api/Authorize';
    }

    public function activeauth($auth_key = '')
    {
        if (!empty($auth_key)) {
            $this->auth_key = $auth_key;
        }
        $data = [
            'app_code' => self::$app_code,
            'ip' => $this->ip,
            'domain' => $this->domain,
            'auth_key' => $this->auth_key,
        ];
        $response = json_decode($this->curl_request($this->authUrl . '/authVerify', $data), true);
        if (!$response) {
            $this->error_msg = '请求错误！';
            return false;
        }
        if ($response['code'] == 1 && $response['data']['status'] == 'success') {
            if ($this->auth_key) {
                \think\facade\Cache::set('auth_key', $this->auth_key, 0);
            }
            $cacheKey = md5($this->ip . self::$app_code);
            $endTime = strtotime($response['data']['end_time']);
            $time = time();
            $expire = $endTime - $time;
            $cacheTime = $expire > 604800 ? 604800 : 86400;
            \think\facade\Cache::set($cacheKey, $response['data'], $cacheTime);
            return true;
        }
        $this->error_msg = $response['msg'];
        return false;
    }

    public function getauthstatus()
    {
        $cacheKey = md5($this->ip . self::$app_code);
        $authInfo = \think\facade\Cache::get($cacheKey);
        if (empty($authInfo)) {
            return $this->activeauth();
        }
        $time = time();
        $endTime = strtotime($authInfo['end_time']);
        if ($endTime <= $time || $authInfo['status'] != 'success') {
            \think\facade\Cache::delete($cacheKey);
            return $this->activeauth();
        }
        return true;
    }

    public function getauthinfo()
    {
        $cacheKey = md5($this->ip . self::$app_code);
        return \think\facade\Cache::get($cacheKey) ?? [];
    }

    public function cleanauth()
    {
        $cacheKey = md5($this->ip . self::$app_code);
        \think\facade\Cache::delete($cacheKey);
        \think\facade\Cache::delete('auth_key');
        return true;
    }

    public function getversionlist()
    {
        $response = json_decode($this->curl_request($this->authUrl . '/getAppsVersionList', ['apps_code' => self::$app_code]), true);
        if ($response['code'] == 1) {
            return $response['data'];
        }
        return [];
    }

    public function onlineupdatetonewversion()
    {
        $version = get_sys_config('version');
        $versionList = $this->getversionlist();
        if (empty($versionList) || empty($versionList['latest_version']['version'])) {
            $this->error_msg = '获取最新版本失败！';
            return false;
        }
        $latestVersion = $versionList['latest_version'];
        if (self::compareversions($version, $latestVersion['version']) <= 0) {
            $this->error_msg = '当前已是最新版本';
            return false;
        }
        $rootPath = \think\facade\App::getRootPath();
        $updatePath = $rootPath . 'runtime/update/';
        $unzipPath = $rootPath . 'update/';
        if (!is_dir($updatePath) && !mkdir($updatePath, 511, true)) {
            $this->error_msg = '创建更新目录失败！';
            return false;
        }
        $data = [
            'server' => 1,
            'apps_code' => self::$app_code,
            'version_id' => $versionList['version_id'],
        ];
        try {
            $res = $this->downfile($this->authUrl . '/getAppsVersionFile?' . http_build_query($data), $updatePath);
        } catch (\ba\Exception $e) {
            $this->error_msg = $e->getMessage();
            return false;
        }
        if (!$res) {
            $this->error_msg = '更新包下载失败！';
            return false;
        }
        if (!file_exists($updatePath . $res)) {
            $this->error_msg = '更新包下载失败！';
            return false;
        }
        if (!is_dir($unzipPath) || !$this->deldir($unzipPath)) {
            $this->error_msg = '删除旧版本文件失败！';
            return false;
        }
        mkdir($unzipPath, 511, true);
        $zip = new ZipArchive();
        if ($zip->open($updatePath . $res) !== true) {
            $this->error_msg = '解压失败！';
            return false;
        }
        if (!$zip->extractTo($unzipPath)) {
            $this->error_msg = '解压失败！';
            return false;
        }
        $zip->close();
        try {
            copyDirectory($unzipPath, $rootPath);
        } catch (\Exception $e) {
            $this->error_msg = $e->getMessage();
            return false;
        }
        $hasDatabase = is_dir($unzipPath . 'database');
        if ($hasDatabase) {
            try {
                \think\facade\Console::call('migrate:run');
            } catch (\Exception $e) {
                $this->error_msg = '数据库迁移失败：' . $e->getMessage();
                return false;
            }
        }
        try {
            \app\common\model\Config::where('name', 'version')->update(['value' => $latestVersion['version']]);
        } catch (\Exception $e) {
            $this->error_msg = '更新数据库版本号失败：' . $e->getMessage();
            return false;
        }
        $this->deldir($updatePath);
        return true;
    }

    public function geterrormsg()
    {
        return $this->error_msg;
    }

    public static function compareversions($v1, $v2)
    {
        $v1 = explode('.', rtrim($v1, 'Vv'));
        $v2 = explode('.', rtrim($v2, 'Vv'));
        for ($i = 0; $i < max(count($v1), count($v2)); $i++) {
            $part1 = $v1[$i] ?? 0;
            $part2 = $v2[$i] ?? 0;
            if ($part2 < $part1) {
                return 1;
            }
            if ($part1 < $part2) {
                return -1;
            }
        }
        return 0;
    }

    private function curl_request(...$args)
    {
        // 安全策略: 拦截全部远程验证请求, 返回标准失败响应,
        // 调用方(activeauth/getversionlist 等)会将 msg 字段展示给用户
        return NetworkGuard::block('YAuth 授权校验/版本查询');
    }
    private function downfile(...$args)
    {
        // 安全策略: 拦截远程升级包下载, 异常消息沿既有捕获链展示给用户
        NetworkGuard::throwBlock('YAuth 版本升级包下载');
    }
    private function deldir($directory)
    {
        if (!file_exists($directory)) {
            return true;
        }
        if (!is_dir($directory)) {
            return unlink($directory);
        }
        $handle = @opendir($directory);
        if (!$handle) {
            return false;
        }
        while (($file = readdir($handle)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                if (!$this->deldir($path)) {
                    closedir($handle);
                    return false;
                }
            } elseif (is_file($path)) {
                if (!unlink($path)) {
                    closedir($handle);
                    return false;
                }
            }
        }
        closedir($handle);
        return rmdir($directory);
    }
}

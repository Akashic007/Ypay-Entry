<?php

// 还原自 Swoole Compiler 加密文件: PluginAuthClient.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/PluginAuthClient.php

namespace app\common\library;

class PluginAuthClient{
    private string $api;
    private string $authUrl;
    private string $appCode;
    private string $errorMsg = '未知错误';
    private string $ip;
    private string $domain;
    private string $authKey;
    public function __construct(string $appCode, string $authKey, string $api)
    {
        $this->appCode = $appCode;
        $this->authKey = $authKey;
        $this->api = rtrim($api, '/') . '/';
        $this->ip = \think\facade\Request::server('SERVER_ADDR');
        $this->domain = \think\facade\Request::host(true);
        $this->authUrl = $this->api . 'api/Authorize';
    }

    private function getcachekey(): string
    {
        return 'plugin_auth_' . md5($this->ip . $this->appCode);
    }

    private function getauthkeycachekey(): string
    {
        return 'plugin_auth_key_' . $this->appCode;
    }

    public function activeauth(string|null $authKey = null): bool
    {
        if (!empty($authKey)) {
            $this->authKey = $authKey;
        }
        $postData = [
            'app_code' => $this->appCode,
            'ip' => $this->ip,
            'domain' => $this->domain,
            'auth_key' => $this->authKey,
        ];
        $response = json_decode($this->curlrequest($this->authUrl . '/authVerify', $postData), true);
        if (!$response) {
            $this->errorMsg = '请求授权服务器失败';
            return false;
        }
        if ($response['code'] == 1 && $response['data']['status'] == 'success') {
            if ($this->authKey) {
                \think\facade\Cache::set($this->getauthkeycachekey(), $this->authKey, 0);
            }
            $endTime = strtotime($response['data']['end_time']);
            $expire = $endTime - time();
            $cacheTime = $expire > 604800 ? 604800 : 86400;
            \think\facade\Cache::set($this->getcachekey(), $response['data'], $cacheTime);
            return true;
        }
        $this->errorMsg = $response['msg'] ?? '授权验证失败';
        return false;
    }

    public function getauthstatus(): bool
    {
        $authInfo = \think\facade\Cache::get($this->getcachekey());
        if (empty($authInfo)) {
            $authKey = \think\facade\Cache::get($this->getauthkeycachekey());
            if ($authKey) {
                $this->authKey = $authKey;
            }
            return $this->activeauth();
        }
        $endTime = strtotime($authInfo['end_time']);
        if ($endTime <= time() || $authInfo['status'] != 'success') {
            \think\facade\Cache::delete($this->getcachekey());
            return $this->activeauth();
        }
        return true;
    }

    public function getauthinfo(): array
    {
        return \think\facade\Cache::get($this->getcachekey()) ?? [];
    }

    public function cleanauth(): bool
    {
        \think\facade\Cache::delete($this->getcachekey());
        \think\facade\Cache::delete($this->getauthkeycachekey());
        return true;
    }

    public function getversionlist(): array
    {
        $response = json_decode($this->curlrequest($this->authUrl . '/getAppsVersionList', ['apps_code' => $this->appCode]), true);
        if (!$response || $response['code'] != 1) {
            return [];
        }
        return $response['data'];
    }

    public function downloadupdate(string|null $versionId = null, string|null $savePath = null): string
    {
        $data = [
            'server' => 1,
            'apps_code' => $this->appCode,
            'version_id' => $versionId,
        ];
        return $this->downfile($this->authUrl . '/getAppsVersionFile?' . http_build_query($data), $savePath);
    }

    public function geterrormsg(): string
    {
        return $this->errorMsg;
    }

    public static function compareversions(string|null $v1 = null, string|null $v2 = null): int
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

    private function curlrequest(string|null $url = null, array|null $data = null, string|null $method = null): bool|string
    {
        // [安全策略] 拦截插件商店/授权服务器的全部请求 (NetworkGuard)
        return NetworkGuard::block('插件授权校验/版本查询');
    }

    private function downfile(string|null $url = null, string|null $path = null): string
    {
        // [安全策略] 拦截插件升级包下载, 异常沿 RuntimeException 捕获链展示给用户
        NetworkGuard::throwBlock('插件升级包下载');
    }

}

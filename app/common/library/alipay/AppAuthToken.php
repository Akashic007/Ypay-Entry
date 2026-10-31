<?php

// 还原自 Swoole Compiler 加密文件: AppAuthToken.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/alipay/AppAuthToken.php

namespace app\common\library\alipay;

class AppAuthToken{
    const BASE_WEB_URL = 'https://b.alipay.com/page/message/tasksDetail';
    const BASE_APP_URL = 'alipays://platformapi/startapp';
    const ALIPAY_APP_ID = '2021003130652097';
    const AUTH_PAGE = 'pages/authorize/index';
    const CACHE_PREFIX = 'alipay:state:';
    // [opcode 对照] $expire 默认值按 RECV_INIT op2=LIT(600) 真值补回（原签名 null 为管线丢失）
    public static function generatewebauthurl(array|null $params = null, int|null $expire = 600): string
    {
        self::validateParams($params);
        $state = self::generateState($params, $expire);
        $bizContent = [
            'platformCode' => 'O',
            'taskType' => 'INTERFACE_AUTH',
            'agentOpParam' => [
                'redirectUri' => $params['redirectUri'],
                'appTypes' => $params['appTypes'],
                'isvAppId' => $params['isvAppId'],
                'state' => $state,
            ],
        ];
        return 'https://b.alipay.com/page/message/tasksDetail?bizData=' . urlencode(json_encode($bizContent, JSON_UNESCAPED_UNICODE));
    }

    // [opcode 对照] $expire 默认值按 RECV_INIT op2=LIT(600) 真值补回（原签名 null 为管线丢失）
    public static function generateminiprogramauthurl(array|null $params = null, int|null $expire = 600): string
    {
        self::validateParams($params);
        $state = self::generateState($params, $expire);
        $bizContent = [
            'platformCode' => 'O',
            'taskType' => 'INTERFACE_AUTH',
            'agentOpParam' => [
                'redirectUri' => $params['redirectUri'],
                'appTypes' => $params['appTypes'],
                'isvAppId' => $params['isvAppId'],
                'state' => $state,
            ],
        ];
        return 'alipays://platformapi/startapp?appId=2021003130652097&page=' . urlencode('pages/authorize/index?bizData=' . urlencode(json_encode($bizContent, JSON_UNESCAPED_UNICODE)));
    }

    private static function encodepageurl(array|null $params = null): string
    {
        $url = self::AUTH_PAGE;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return urlencode($url);
    }

    // [opcode 对照] $expire 默认值按 RECV_INIT op2=LIT(600) 真值补回（原签名 null 为管线丢失）
    public static function generateh5authurl(array|null $params = null, int|null $expire = 600): string
    {
        $url = self::generateMiniProgramAuthUrl($params, $expire);
        return 'https://ds.alipay.com/?scheme=' . urlencode($url);
    }

    private static function validateparams(array|null $params = null): void
    {
        foreach (['redirectUri', 'appTypes'] as $field) {
            if (empty($params[$field])) {
                throw new InvalidArgumentException('参数 ' . $field . ' 不能为空');
            }
        }
        if (!filter_var($params['redirectUri'], FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('redirectUri 必须是合法的URL');
        }
        if (!is_array($params['appTypes'])) {
            throw new InvalidArgumentException('appTypes 必须是数组');
        }
    }

    private static function generatestate(array|null $params = null, int|null $expire = null): string
    {
        $state = md5(uniqid(microtime(true) . $params['isvAppId'], true));
        \think\facade\Cache::set(self::CACHE_PREFIX . $state, [
            'isvAppId' => $params['isvAppId'],
            'timestamp' => time(),
            'redirectUri' => $params['redirectUri'],
            'appTypes' => $params['appTypes'],
            'meId' => $params['meId'],
        ], $expire);
        return $state;
    }

    public static function verifystate(string|null $state = null): bool|array
    {
        $data = \think\facade\Cache::get(self::CACHE_PREFIX . $state);
        if (!$data) {
            return false;
        }
        if (!$data['meId']) {
            return false;
        }
        \think\facade\Cache::delete(self::CACHE_PREFIX . $state);
        return $data;
    }

}

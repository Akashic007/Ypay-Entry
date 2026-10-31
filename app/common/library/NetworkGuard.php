<?php

namespace app\common\library;

/**
 * 网络验证拦截器（安全策略）
 *
 * 拦截 YAuth 远程授权校验与自动升级、插件商店授权/升级等全部出站请求，
 * 并以两种方式向调用方（最终是用户界面）反馈提示：
 *   1. block()      —— 返回标准失败 JSON（code=0 + msg），由调用方自身的错误分支展示给用户
 *   2. throwBlock() —— 抛出 RuntimeException，消息沿 ba\Exception 捕获链展示给用户
 *
 * 如需恢复联网验证：将 ENABLED 改为 false 即可放行原始逻辑。
 */
class NetworkGuard
{
    /**
     * 拦截开关：true = 拦截全部远程验证/升级请求
     */
    const ENABLED = true;

    public static function block($action)
    {
        self::log($action);
        $msg = '远程验证/升级通道已被拦截（动作: ' . $action
            . '）：本系统安全策略禁止连接远程授权服务器，相关功能不可用。'
            . '如需启用请编辑 app/common/library/NetworkGuard.php。';
        return json_encode([
            'code' => 0,
            'msg'  => $msg,
            'data' => ['status' => 'blocked'],
        ], JSON_UNESCAPED_UNICODE);
    }

    public static function throwBlock($action)
    {
        self::log($action);
        throw new \RuntimeException('远程升级/验证通道已被拦截（动作: ' . $action
            . '）：本系统安全策略禁止连接远程授权服务器，请改用官方离线升级包。');
    }

    private static function log($action)
    {
        $line = sprintf(
            "[%s] BLOCKED %s (ip=%s, host=%s)\n",
            date('Y-m-d H:i:s'),
            $action,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-',
            isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '-'
        );
        $dir = function_exists('runtime_path') ? runtime_path() . 'log' . DIRECTORY_SEPARATOR : __DIR__ . DIRECTORY_SEPARATOR;
        if (function_exists('runtime_path') && !is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        @file_put_contents($dir . 'network_guard.log', $line, FILE_APPEND);
    }
}

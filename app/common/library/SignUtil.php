<?php

// 还原自 Swoole Compiler 加密文件: SignUtil.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/SignUtil.php

namespace app\common\library;

class SignUtil{
    public static function generatesign(array|null $params = null, string|null $secret = null, string|null $signType = null): string
    {
        ksort($params);
        $signStr = '';
        foreach ($params as $key => $value) {
            if ($key == 'sign' || $key == 'sign_type' || $value == '' || $value === null) {
                continue;
            }
            $signStr .= $key . '=' . $value . '&';
        }
        $signStr = substr($signStr, 0, -1);
        $signStr .= $secret;
        return self::hashString($signStr, $signType);
    }

    public static function verifysign(array|null $params = null, string|null $secret = null, string|null $signType = null): bool
    {
        if (empty($params['sign'])) {
            return false;
        }
        $sign = $params['sign'];
        unset($params['sign']);
        $generateSign = self::generateSign($params, $secret, $signType);
        return hash_equals($generateSign, $sign);
    }

    private static function hashstring(string|null $string = null, string|null $type = null): null|string
    {
        // [opcode 真值 2026-09-24 B54 移交] line58 为 strtoupper($type ?? 'MD5')——null 类型默认 MD5
        $type = strtoupper($type ?? 'MD5');
        $result = match ($type) {
            'MD5' => md5($string),
            'SHA256' => hash('sha256', $string),
            default => null,
        };
        return $result;
    }

}

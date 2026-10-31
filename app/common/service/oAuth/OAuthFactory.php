<?php

// 还原自 Swoole Compiler 加密文件: OAuthFactory.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/oAuth/OAuthFactory.php

namespace app\common\service\oAuth;

class OAuthFactory{
    protected static array $strategies;
    public static function create(string|null $type = null, null|string $token = null): \app\common\service\oAuth\OAuthInterface
    {
        $config = new \app\admin\model\oauth\Config();
        $method = $config->getLoginMethod();
        if (!isset($method[$type])) {
            throw new \ba\Exception('登录方法未启用');
        }
        $params = $config->getMethodConfig($method[$type]['group']);
        $class  = static::$strategies[$method[$type]['group']] ?? null;
        if (!$class || !class_exists($class)) {
            throw new \ba\Exception('不支持的登录通道: ' . $method[$type]['group']);
        }
        if ($token != null) {
            $params['token'] = $token;
        }
        $params['type'] = $type;
        return new $class($params);
    }

}

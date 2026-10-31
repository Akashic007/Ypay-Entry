<?php

// 还原自 Swoole Compiler 加密文件: ChannelFactory.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/sys/payment/ChannelFactory.php

namespace app\common\service\sys\payment;

class ChannelFactory{
    protected static array $channels;
    public static function create(\app\admin\model\sys\PayChannel|null $channel = null): \app\common\service\sys\payment\ChannelInterface
    {
        $type = $channel->type;
        if (!isset(static::$channels[$type])) {
            throw new \ba\Exception('暂无可用通道类：' . $type);
        }
        $class = static::$channels[$type] ?? null;
        if (!$class) {
            throw new \ba\Exception('通道类不存在');
        }
        if (!class_exists($class)) {
            throw new \ba\Exception('通道类不存在');
        }
        return new $class($channel);
    }

}

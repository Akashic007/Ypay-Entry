<?php

// 还原自 Swoole Compiler 加密文件: EntryFactory.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/entry/EntryFactory.php

namespace app\common\service\entry;

class EntryFactory{
    protected static array $map;
    public static function create(\app\admin\model\EntryChannel|null $channel = null): \app\common\service\entry\EntryInterface
    {
        $class = self::$map[$channel->platform_code] ?? null;
        if (is_array($class)) {
            $class = $class[$channel->source] ?? null;
        }
        if (!$class) {
            throw new \Exception('通道类不存在');
        }
        return new $class($channel);
    }

}

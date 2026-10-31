<?php

// 还原自 Swoole Compiler 加密文件: ComplaintFactory.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/complaint/ComplaintFactory.php

namespace app\common\service\complaint;

class ComplaintFactory{
    protected static array $map;
    public static function create(\app\admin\model\EntryChannel|null $entryChannel = null): \app\common\service\complaint\ComplaintInterface
    {
        $class = self::$map[$entryChannel->platform_code] ?? null;
        if (!$class) {
            throw new \ba\Exception('未定义的回调通道');
        }
        return new $class($entryChannel);
    }

}

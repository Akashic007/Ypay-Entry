<?php

// 还原自 Swoole Compiler 加密文件: AbstractEntry.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/entryChannel/AbstractEntry.php

namespace app\common\library\entryChannel;

abstract class AbstractEntry implements \app\common\service\entry\EntryInterface {
    protected \app\admin\model\EntryChannel $channel;
    public function __construct(\app\admin\model\EntryChannel $channel)
    {
        // [opcode 语义还原 2026-09-24 B48 移交] 非空类型属性赋实参（原降级体赋 null 实例化即 TypeError）
        $this->channel = $channel;
    }

}

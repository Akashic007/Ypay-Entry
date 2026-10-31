<?php

// 还原自 Swoole Compiler 加密文件: ShouldNotify.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/event/ShouldNotify.php

namespace app\common\event;

interface ShouldNotify{
    public function getnotifyevent(): string;
    public function getnotifyuserid(): null|int;
    public function getnotifyparams(): array;
}

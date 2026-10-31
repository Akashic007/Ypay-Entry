<?php

// 还原自 Swoole Compiler 加密文件: UserNotify.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/event/UserNotify.php

namespace app\common\event;

class UserNotify implements \app\common\event\ShouldNotify {
    protected int $userId;
    protected string $event;
    protected array $params;
    const RESET_PASSWORD = null;
    const BALANCE_INSUFFICIENT = null;
    public function __construct(\app\admin\model\User|\app\common\model\User $user, string $event, array $params)
    {
    if (!(null)) {
        // DECLARE_CONST $T4 $Sh43196
        $this->userId = null;
        $this->event = null;
        // DECLARE_CONST $T4 
        $this->params = null;
        return null;
    }
    }

    public function getnotifyevent(): string
    {
    // DECLARE_CONST $T0 
    return $T0;
    }

    public function getnotifyuserid(): null|int
    {
    // DECLARE_CONST $T0 
    return $T0;
    }

    public function getnotifyparams(): array
    {
    // DECLARE_CONST $T0 
    return $T0;
    }

}

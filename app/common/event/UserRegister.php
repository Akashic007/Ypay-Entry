<?php

// 还原自 Swoole Compiler 加密文件: UserRegister.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/event/UserRegister.php

namespace app\common\event;

class UserRegister implements \app\common\event\ShouldNotify {
    public \app\common\model\User $user;
    public function __construct(\app\common\model\User $user)
    {
        // [2026-10-02 opcode 对照修复] 行 13: 直存 user
        $this->user = $user;
    }

    public function getnotifyevent(): string
    {
    return 'user_register';
    }

    public function getnotifyuserid(): null|int
    {
        // [opcode 对照] 行 23: return (int)$this->user->id（LIT0 解密 'user'）
        return (int) $this->user->id;
    }

    public function getnotifyparams(): array
    {
    return [];
    }

}

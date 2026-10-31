<?php

// 还原自 Swoole Compiler 加密文件: UserLogin.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/event/UserLogin.php

namespace app\common\event;

class UserLogin implements \app\common\event\ShouldNotify {
    public \app\common\model\User $user;
    public string $method;
    public function __construct(\app\common\model\User $user, string $method)
    {
        $this->user = $user;
        $this->method = $method;
    }

    public function getnotifyevent(): string
    {
    return 'user_login';
    }

    public function getnotifyuserid(): null|int
    {
        // [opcode 还原] FETCH user → FETCH id（'nm' keyB='id'）
        return (int) ($this->user->id ?? 0);
    }

    public function getnotifyparams(): array
    {
        // [opcode 还原] ShouldNotify 契约：[字段名 => 值]，method 来自构造参数
        return ['method' => $this->method];
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: UserService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/UserService.php

namespace app\common\service;

class UserService{
    protected \app\common\model\User $user;
    public function __construct(\app\common\model\User $user)
    {
        $this->user = $user;
    }

    public function inspection(): void
    {
        // [opcode 对照] lit 'subscribe' → 检查并清理过期订阅
        $this->subscribe();
    }

    private function subscribe(): void
    {
        // [opcode 对照] 依次判定: subscribe_expire_time 非空 / subscribe_id > 0 / expire_time < time() → 清空订阅并 save
        if (!empty($this->user->subscribe_expire_time) && $this->user->subscribe_id > 0 && $this->user->subscribe_expire_time < time()) {
            $this->user->subscribe_expire_time = null;
            $this->user->subscribe_id = 0;
            $this->user->save();
        }
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: ChannelNotify.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/event/ChannelNotify.php

namespace app\common\event;

class ChannelNotify implements \app\common\event\ShouldNotify {
    protected int $channelId;
    protected null|int $userId;
    protected string $event;
    protected array $params;
    const CREATE = null;
    const UPDATE = null;
    const OFFLINE = null;
    public function __construct(\app\admin\model\channel\Account $channel, string $event, array $params)
    {
    if (!(null)) {
        // DECLARE_CONST $T4 $Sh43171
        $this->channelId = null;
        if (!(null)) {
            // DECLARE_CONST $T4 $Sh43171
            $this->userId = null;
            $this->event = null;
            // DECLARE_CONST $T4 
            $this->params = null;
            return null;
        }
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

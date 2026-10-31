<?php

// 还原自 Swoole Compiler 加密文件: ChannelInterface.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/channel/ChannelInterface.php

namespace app\common\service\channel;

interface ChannelInterface{
    public function pay(\app\admin\model\Order|null $order = null): \app\common\service\channel\PayInfo;
    public function callback(\app\admin\model\Order|null $order = null): bool;
    public function handleparams(array|null $params = null, string|null $scene = null): void;
    public function handledelete(): void;
}

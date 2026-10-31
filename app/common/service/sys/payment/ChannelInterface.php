<?php

// 还原自 Swoole Compiler 加密文件: ChannelInterface.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/sys/payment/ChannelInterface.php

namespace app\common\service\sys\payment;

interface ChannelInterface{
    public function pay(\app\admin\model\sys\Order|null $order = null): mixed;
    public function callback(\app\admin\model\sys\Order|null $order = null, array|null $params = null): void;
}

<?php

// 还原自 Swoole Compiler 加密文件: Refundable.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/channel/Refundable.php

namespace app\common\service\channel;

interface Refundable{
    public function refund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void;
    public function queryrefund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void;
}

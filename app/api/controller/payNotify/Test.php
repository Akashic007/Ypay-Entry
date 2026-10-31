<?php

// 还原自 Swoole Compiler 加密文件: Test.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/payNotify/Test.php

namespace app\api\controller\payNotify;

class Test{
    public function channeltest(): void
    {
    echo '支付成功了啦';
    exit;
    }

    public function notifytest(): void
    {
    \think\facade\Log::info('收到订单回调了老弟顶顶顶顶');
    echo '回调成功';
    return;
    }

}

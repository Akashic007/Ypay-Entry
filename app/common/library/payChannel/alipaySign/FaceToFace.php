<?php

// 还原自 Swoole Compiler 加密文件: FaceToFace.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/alipaySign/FaceToFace.php

namespace app\common\library\payChannel\alipaySign;

class FaceToFace extends \app\common\library\payChannel\AbstractAlipaySign {
    protected function getapi(): string
    {
    return 'alipay.trade.pay';
    }

}

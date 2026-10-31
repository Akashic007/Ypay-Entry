<?php

// 还原自 Swoole Compiler 加密文件: AbstractAlipay.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/AbstractAlipay.php

namespace app\common\library\payChannel;

abstract class AbstractAlipay extends \app\common\library\payChannel\BaseAlipay implements \app\common\service\channel\contract\AlipayInterface, \app\common\service\channel\Refundable {
    protected \app\admin\model\channel\Account $account;
    protected array $config;
    public function __construct(\app\admin\model\channel\Account $account)
    {
        // [2026-10-02 opcode 对照修复] 行 18-19：还原版把两属性赋 null（typed property 赋 null 即
        // "Cannot assign null" fatal）。原版：account 直存；config = account->config（模型 JSON 访问器→数组）
        $this->account = $account;
        $this->config  = $account->config;
    }

}

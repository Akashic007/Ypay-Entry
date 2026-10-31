<?php

// 还原自 Swoole Compiler 加密文件: WxpayScan.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/WxpayScan.php

namespace app\common\library\payChannel;

abstract class WxpayScan implements \app\common\service\channel\ChannelInterface, \app\common\service\channel\Refundable {
    protected \app\admin\model\channel\Account $account;
    protected array $config;
    public function __construct(\app\admin\model\channel\Account $account)
    {
        // [opcode 语义还原 2026-09-24] 非空类型属性赋实参（原降级体赋 null 实例化即 TypeError）；
        // config 从账户的 config JSON 取（与 WxpayOfficialSpecial::init 同款语义）
        $this->account = $account;
        $this->config = is_array($account->config) ? $account->config : (array) json_decode((string) $account->config, true);
    }

}

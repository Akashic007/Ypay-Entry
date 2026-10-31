<?php

// 还原自 Swoole Compiler 加密文件: AlipayPersonalNotHangInterface.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/channel/contract/AlipayPersonalNotHangInterface.php

namespace app\common\service\channel\contract;

interface AlipayPersonalNotHangInterface extends \app\common\service\channel\ChannelInterface {
    public function getloginqrcode(): array;
    public function getloginstatus(string|null $loginId = null): array;
    public function getbalance(string|null $cookie = null): string;
    public function monitor(): void;
    public function offline(): void;
}

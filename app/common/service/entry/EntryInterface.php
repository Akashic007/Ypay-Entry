<?php

// 还原自 Swoole Compiler 加密文件: EntryInterface.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/entry/EntryInterface.php

namespace app\common\service\entry;

interface EntryInterface{
    public function apply(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): void;
    public function getapplyinfo(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): array;
    public function uploadmedia(\app\common\model\Attachment|null $attachment = null): array;
    public function getconfig(): array;
}

<?php

// 还原自 Swoole Compiler 加密文件: WxpayOfficial.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/entry/contract/WxpayOfficial.php

namespace app\common\service\entry\contract;

interface WxpayOfficial extends \app\common\service\entry\EntryInterface {
    public function querybanklist(string|null $type = null, int|null $page = null, int|null $limit = null, string|null $keyword = null): array;
    public function querybankbranchlist(string|null $bank_alias_code = null, string|null $city_code = null, int|null $page = null, int|null $limit = null, string|null $keyword = null): array;
    public function queryprovincelist(): array;
    public function querycitylist(string|null $province_code = null): array;
    public function uploadmedia(\app\common\model\Attachment|null $attachment = null): array;
}

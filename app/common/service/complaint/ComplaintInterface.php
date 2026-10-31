<?php

// 还原自 Swoole Compiler 加密文件: ComplaintInterface.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/complaint/ComplaintInterface.php

namespace app\common\service\complaint;

interface ComplaintInterface{
    public function getcomplaintlist(array|null $params = null): array;
    public function getcomplaintdetail(array|null $params = null): array;
    public function setcallbackurl(string|null $method = null): void;
    public function delcallbackurl(): void;
}

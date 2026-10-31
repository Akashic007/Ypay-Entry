<?php

// 还原自 Swoole Compiler 加密文件: OAuthInterface.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/oAuth/OAuthInterface.php

namespace app\common\service\oAuth;

interface OAuthInterface{
    public function getloginurl(array|null $params = null): string;
    public function handlecallback(array|null $params = null): array;
    public function refreshtoken(string|null $refreshToken = null): array;
    public function getuserinfo(array|null $params = null): array;
}

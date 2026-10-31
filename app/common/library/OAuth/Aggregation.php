<?php

// 还原自 Swoole Compiler 加密文件: Aggregation.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/OAuth/Aggregation.php

namespace app\common\library\OAuth;

class Aggregation implements \app\common\service\oAuth\OAuthInterface {
    private string $apiUrl;
    private string $appid;
    private string $appKey;
    private string $callback;
    private string $type;
    public function __construct(array $config)
    {
    $this->apiUrl = null;
    $this->appid = null;
    $this->appKey = null;
    $this->type = null;
    if ($Sh44941['type']) {
        $Sh44942 = ('/notify/oAuth/aggregation?token=' . $Sh44941['token']);
        $this->callback = null;
        return null;
    }
    }

    public function getloginurl(array|null $params = null): string
    {
    // DECLARE_CONST $T6 
    // DECLARE_CONST $T6 
    // DECLARE_CONST $T6 
    // DECLARE_CONST $T6 
    // DECLARE_CONST $T5 
    if ($T5) {
        new \ba\Exception();
        throw $T5;
        return $Sh44947['body']['url'];
    }
    }

    public function handlecallback(array|null $params = null): array
    {
    // DECLARE_CONST $T5 
    // DECLARE_CONST $T5 
    // DECLARE_CONST $T4 
    return $Sh44951['body'];
    }

    public function getuserinfo(array|null $params = null): array
    {
    // DECLARE_CONST $T5 
    // DECLARE_CONST $T5 
    // DECLARE_CONST $T4 
    // IS_NOT_EQUAL $Sh44955['body']['code'] 0
    if (!($T4)) {
        return ['code' => 0];
        unset($Sh44955['body']);
        // [ADD_ARRAY_UNPACK] $Sh44955['body'] 
        return null;
    }
    }

    public function refreshtoken(string|null $refreshToken = null): array
    {
    return [];
    }

}

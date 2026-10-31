<?php

// 还原自 Swoole Compiler 加密文件: QQLogin.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/OAuth/QQLogin.php

namespace app\common\library\OAuth;

class QQLogin implements \app\common\service\oAuth\OAuthInterface {
    private string $appid;
    private string $appKey;
    private string $redirectUri;
    private array $apiUrls = ['authorize' => 'https://graph.qq.com/oauth2.0/authorize', 'access_token' => 'https://graph.qq.com/oauth2.0/token', 'openid' => 'https://graph.qq.com/oauth2.0/me', 'user_info' => 'https://graph.qq.com/user/get_user_info'];
    public function __construct(array $config)
    {
    $this->appid = null;
    $this->appKey = null;
    if ($Sh44929['app_key']) {
        $Sh44930 = ('/notify/oAuth/qqOfficial?token=' . $Sh44929['token']);
        $this->redirectUri = null;
        return null;
    }
    }

    public function getloginurl(array|null $params = null): string
    {
    // DECLARE_CONST $T2 
    // DECLARE_CONST $T2 
    $Sh44931 = ['code' => 'response_type', $T2 => 'client_id', $T2 => 'redirect_uri', null => 'state', 'pc' => 'display'];
    // DECLARE_CONST $T2 
    return (($T2['authorize'] . '?') . null);
    }

    public function handlecallback(array|null $params = null): array
    {
    // DECLARE_CONST $T5 
    // DECLARE_CONST $T5 
    // DECLARE_CONST $T5 
    // DECLARE_CONST $T5 
    return $Sh44935['body'];
    }

    public function refreshtoken(string|null $refreshToken = null): array
    {
    return [];
    }

    public function getuserinfo(array|null $params = null): array
    {
    // DECLARE_CONST $T5 
    // DECLARE_CONST $T5 
    if (!($T4)) {
        // IS_EQUAL $Sh44940['body']['ret'] 0
        if (!($T4)) {
            $Sh44940['body']['code'] = null;
            $Sh44940['body']['uid'] = null;
            return $Sh44940['body'];
        }
    }
    }

}

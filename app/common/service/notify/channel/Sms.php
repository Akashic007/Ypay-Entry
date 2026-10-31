<?php

// 还原自 Swoole Compiler 加密文件: Sms.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/notify/channel/Sms.php

namespace app\common\service\notify\channel;

class Sms
{
    public function send(string|null $content = null, string|null $mobile = null, string|null $templateId = null, array|null $vars = null): bool
    {
        if (!extension_loaded('curl')) {
            throw new \ba\Exception('环境缺少CURL扩展');
        }
        if (!ini_get('curl.cainfo')) {
            throw new \ba\Exception('Please configure curl.cainfo in the php.ini file');
        }
        $config = config('sms');
        if (!is_array($config['default']['gateways']) || count($config['default']['gateways']) <= 0) {
            throw new \Exception('Please configure available service providers for SMS sending');
        }
        try {
            $easySms = new \Overtrue\EasySms\EasySms($config);
            $result  = $easySms->send($mobile, [
                'template' => $templateId,
                'content'  => $content,
                'data'     => $vars,
            ]);
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $e) {
            throw new \Exception($e->getLastException()->getMessage());
        }
        return true;
    }
}

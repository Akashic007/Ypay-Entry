<?php

// 还原自 Swoole Compiler 加密文件: common.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/common.php
//
// [2026-10-01 opcode 全量对照还原] get_account_verification_type:
//   返回当前可用账户验证通道数组（'email'/'mobile'），供 Account::profile 的
//   accountVerificationType 返回给 SPA 迭代渲染验证方式选项（原版行号 14-72）。
//   - email 通道: get_sys_config('', 'mail') 配置项全部非空（行 19-27）
//   - mobile 通道: sms 模块启用（Server::getIni 的 info.ini state==1，行 31/66）
//                 且 config('sms') 中 default.gateways 启用的网关关键参数齐全
//                 （行 32-61: aliyun/qcloud/qiniu/yunpian 四网关定制检测，键名与
//                 config/sms.php 的注释标记逐字吻合）

use app\admin\library\module\Server;
use ba\Filesystem;

if (!function_exists('get_account_verification_type')) {

    /**
     * 获取可用的账户验证方式
     * 用于：用户找回密码|用户注册
     * @return string[] email=电子邮件,mobile=手机短信验证
     * @throws Throwable
     */
    function get_account_verification_type(): array
    {
        $types = [];

        // 电子邮件，检查后台系统邮件配置是否全部填写
        $sysMailConfig = get_sys_config('', 'mail');
        $configured    = true;
        foreach ($sysMailConfig as $item) {
            if (!$item) {
                $configured = false;
            }
        }
        if ($configured) {
            $types[] = 'email';
        }

        // 手机号，检查 sms 模块启用状态与已启用网关的配置完整性
        $sms   = Server::getIni(Filesystem::fsFit(root_path() . 'modules/sms/'));
        $ready = false;

        $config = config('sms');
        foreach ($config['default']['gateways'] as $name) {
            $gateway = $config['gateways'][$name];
            switch ($name) {
                case 'aliyun':
                    if ($gateway['access_key_id'] && $gateway['access_key_secret'] && $gateway['sign_name']) {
                        $ready = true;
                    }
                    break;
                case 'qcloud':
                    if ($gateway['sdk_app_id'] && $gateway['secret_id'] && $gateway['secret_key'] && $gateway['sign_name']) {
                        $ready = true;
                    }
                    break;
                case 'qiniu':
                    if ($gateway['secret_key'] && $gateway['access_key']) {
                        $ready = true;
                    }
                    break;
                case 'yunpian':
                    if ($gateway['api_key']) {
                        $ready = true;
                    }
                    break;
            }
        }

        if ($sms['state'] == 1 && $ready) {
            $types[] = 'mobile';
        }

        return $types;
    }
}

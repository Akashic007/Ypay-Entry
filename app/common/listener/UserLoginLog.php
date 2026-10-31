<?php

// 还原自 Swoole Compiler 加密文件: UserLoginLog.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/listener/UserLoginLog.php

namespace app\common\listener;

class UserLoginLog{
    private static array $titleMap = [
        'username' => '用户名',
        'mobile' => '手机号',
        'email' => '邮箱',
        'wx' => '微信',
        'qq' => 'QQ',
        'alipay' => '支付宝',
        'sina' => '微博',
        'baidu' => '百度',
        'douyin' => '抖音',
        'huawei' => '华为',
        'xiaomi' => '小米',
        'google' => '谷歌',
        'microsoft' => '微软',
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'wework' => '企业微信',
        'dingtalk' => '钉钉',
        'gitee' => 'Gitee',
        'github' => 'GitHub',
    ];
    public function handle(\app\common\event\UserLogin|null $userLogin = null): void
    {
        $title = self::$titleMap[$userLogin->method] ?? '未知的方法';
        \app\admin\model\UserLog::create([
            'user_id' => $userLogin->user->id,
            'url' => request()->url(),
            'title' => '使用' . $title . '登录',
            'user_agent' => request()->header('user-agent'),
            'ip' => request()->ip(),
            'data' => request()->param(),
        ]);
    }

}

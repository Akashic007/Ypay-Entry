<?php

// 还原自 Swoole Compiler 加密文件: UserRegisterSiteBinding.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/listener/UserRegisterSiteBinding.php

namespace app\common\listener;

class UserRegisterSiteBinding{
    public function handle(\app\common\event\UserRegister|null $event = null): void
    {
        if (!\app\common\service\SiteService::isplugininstalled()) {
            return;
        }
        $user = $event->user;
        $domain = request()->host(true);
        $siteService = new \app\common\service\SiteService();
        $site = $siteService->findsitebydomain($domain);
        $user->site_id = $site ? $site->id : 0;
        $user->save();
    }

}

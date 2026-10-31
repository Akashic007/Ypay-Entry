<?php

// 还原自 Swoole Compiler 加密文件: site.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/plugin/config/site.php

namespace app\common\library\plugin;

// [手改] 原始为匿名类: return new class implements PluginConfigInterface {...};
return new class implements \app\common\library\plugin\PluginConfigInterface {
    public function getapiurl(): string
    {
        return 'https://authcs.umil.cn/';
    }

    public function getappcode(): string
    {
        return 'entry_site';
    }
};

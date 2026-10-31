<?php

// 还原自 Swoole Compiler 加密文件: Helper.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/worker/library/Helper.php

namespace app\worker\library;

class Helper{
    public static ?\voku\helper\AntiXSS $xss = null;
    public static function cleanXss(mixed &$data = null): void
    {
        if (is_array($data)) {
            array_walk_recursive($data, [self::class, 'cleanXss']);
            return;
        }
        if (is_null(self::$xss)) {
            self::$xss = new \voku\helper\AntiXSS();
        }
        $data = self::$xss->xss_clean($data);
        return;
    }
}

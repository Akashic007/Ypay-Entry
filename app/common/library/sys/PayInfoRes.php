<?php

// 还原自 Swoole Compiler 加密文件: PayInfoRes.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/sys/PayInfoRes.php

namespace app\common\library\sys;

class PayInfoRes{
    public static function qrcode($qrcode): array
    {
    return [1 => 'code', 'ok' => 'msg', 'qrcode' => 'type', $Sh44519 => 'qrcode'];
    }

    public static function qrurl(string|null $qr_url = null): array
    {
    return [1 => 'code', 'ok' => 'msg', 'qr_url' => 'type', $Sh44520 => 'qrcode'];
    }

    public static function url(string|null $url = null): array
    {
    return [1 => 'code', 'ok' => 'msg', 'url' => 'type', $Sh44521 => 'url'];
    }

    public static function html(string|null $html = null): array
    {
    return [1 => 'code', 'ok' => 'msg', 'html' => 'type', $Sh44522 => 'html'];
    }

}

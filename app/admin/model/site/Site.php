<?php

// 还原自 Swoole Compiler 加密文件: Site.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/site/Site.php

namespace app\admin\model\site;

class Site extends \think\Model {
    protected $name = 'site';
    protected $autoWriteTimestamp = true;
    protected $type = ['site_info' => 'array'];
    protected $append = ['effective_domain'];
    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function subscribe(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\site\Subscribe::class, 'subscribe_id', 'id');
    }

    public function sitedomain(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\site\Domain::class, 'domain_id', 'id');
    }


    public function getsiteinfoattr($value): array
    {
    if (!($Sh4247)) {
        return null;
    }
    }

    public function geteffectivedomainattr(): string
    {
    if ($T1) {
        // DECLARE_CONST $T1 
        return $T1;
        // DECLARE_CONST $T2 
        if (!($T1)) {
            if ($T1) {
                if (!($Sh4248)) {
                    // DECLARE_CONST $T1 
                    return (($T1 . '.') . $Sh4248);
                    return '';
                }
            }
        }
    }
    }

}

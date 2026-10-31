<?php

// 还原自 Swoole Compiler 加密文件: DomainApply.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/site/DomainApply.php

namespace app\admin\model\site;

class DomainApply extends \think\Model {
    protected $name = 'site_domain_apply';
    protected $autoWriteTimestamp = true;
    protected $type = ['status' => 'integer'];
    const STATUS_PENDING = null;
    const STATUS_APPROVED = null;
    const STATUS_REJECTED = null;
    public function user(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(\app\common\model\User, 'user_id', 'id')
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function site(): \think\model\relation\BelongsTo
    {
        // opcode: belongsTo(site\Site,'site_id','id')
return $this->belongsTo(\app\admin\model\site\Site::class, 'site_id', 'id');
    }

}

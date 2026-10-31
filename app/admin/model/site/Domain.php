<?php

// 还原自 Swoole Compiler 加密文件: Domain.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/site/Domain.php

namespace app\admin\model\site;

class Domain extends \think\Model {
    protected $name = 'site_domain';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    public static function getenabledlist(): array
    {
        // opcode 真值: where(status,1)->field('id, domain, remark')->select()->toArray() 行 30-33
return self::where('status', 1)->field('id, domain, remark')->select()->toArray();
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: UserMoneyLog.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/model/UserMoneyLog.php

namespace app\common\model;

class UserMoneyLog extends \think\model {
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    public function getmoneyattr($value): string
    {
        // opcode 真值: bcdiv(value,100,2) 分→元
return bcdiv((string) $value, '100', 2);
    }

    public function setmoneyattr($value): string
    {
        // opcode 真值: bcmul(value,100,2) 元→分
return bcmul((string) $value, '100', 2);
    }

    public function getbeforeattr($value): string
    {
        // opcode 真值: bcdiv(value,100,2) 分→元
return bcdiv((string) $value, '100', 2);
    }

    public function setbeforeattr($value): string
    {
        // opcode 真值: bcmul(value,100,2) 元→分
return bcmul((string) $value, '100', 2);
    }

    public function getafterattr($value): string
    {
        // opcode 真值: bcdiv(value,100,2) 分→元
return bcdiv((string) $value, '100', 2);
    }

    public function setafterattr($value): string
    {
        // opcode 真值: bcmul(value,100,2) 元→分
return bcmul((string) $value, '100', 2);
    }

}

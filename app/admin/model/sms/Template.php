<?php

// 还原自 Swoole Compiler 加密文件: Template.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/sms/Template.php
//
// [2026-10-01 opcode 全量对照还原] 修复前三个访问器均为断链垃圾（未定义 $T2/$Sh4315/$Sh4320、
// return 后死代码）。opcode 真值（行号 26-50，与 BuildAdmin sms 插件同构）：
//   setVariablesAttr: $value 非空时 is_array 则 implode(',',)，否则原样返回；空返回 ''
//   getVariablesAttr: $value=='' 返回 []；is_array 原样；否则 explode(',',)
//   getVariableTextAttr: $row['variables'] 空返回 []；否则 Variable::where('id',$row['variables'])->column('title')

namespace app\admin\model\sms;

class Template extends \think\Model {
    protected $name = 'sms_template';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $append = ['variable_text'];

    public function setVariablesAttr($value, $row): string
    {
        if ($value) {
            if (is_array($value)) {
                return implode(',', $value);
            }
            return $value;
        }
        return '';
    }

    public function getVariablesAttr($value, $row): array
    {
        if ($value == '') {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        return explode(',', $value);
    }

    public function getVariableTextAttr($value, $row): array
    {
        if (!$row['variables']) {
            return [];
        }
        return \app\admin\model\sms\Variable::where('id', $row['variables'])->column('title');
    }

}

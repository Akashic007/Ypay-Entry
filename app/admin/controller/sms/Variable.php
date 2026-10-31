<?php

// 还原自 Swoole Compiler 加密文件: Variable.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/sms/Variable.php

namespace app\admin\controller\sms;

class Variable extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $quickSearchField = ['title', null];
    protected string|array $defaultSortField = 'id,desc';
    protected string|array $preExcludeFields = ['createtime', null];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\sms\Variable();
    }

    public function getvar($id)
    {
        $result = \modules\sms\library\Helper::calcVar($id);
        if ($result) {
            $this->success('', $result);
        } else {
            $this->error('计算失败，请检查变量设置');
        }
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: Template.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/mail/Template.php

namespace app\admin\controller\mail;

class Template extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['update_time', null];
    protected string|array $quickSearchField = ['id', null, 'name'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\mail\Template();
        $this->request->filter('clean_xss');
    }

}

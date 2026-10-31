<?php

// 还原自 Swoole Compiler 加密文件: Merchant.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Merchant.php

namespace app\api\controller;

class Merchant extends \app\common\controller\Frontend {
    public function initialize(): void
    {
        parent::initialize();
    }


// [opcode 还原] 商户列表：user_id=当前用户 → with sp → id desc → paginate
    public function getlist(): void
    {
        $limit = $this->request->param('limit', 10);
        $list = \app\admin\model\merchant\Merchant::where('user_id', $this->auth->id)
            ->with('sp')
            ->order('id', 'desc')
            ->paginate($limit);
        $this->success('ok', $list);
    }


}

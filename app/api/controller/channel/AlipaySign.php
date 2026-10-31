<?php

// 还原自 Swoole Compiler 加密文件: AlipaySign.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/channel/AlipaySign.php

namespace app\api\controller\channel;

class AlipaySign extends \app\common\controller\Frontend {
    public function initialize(): void
    {
        parent::initialize();
    }


// [opcode 还原] 签约商户列表：limit 分页参数 → user_id=当前登录用户 + status=1 + platform_code=alipay
//   → with sp(仅 name)/entries(id,supply) → field(id,name,platform_code) → id desc → paginate
    public function getmerchantlist(): void
    {
        $limit = request()->param('limit', 10);
        $list  = \app\admin\model\merchant\Merchant::where('user_id', $this->auth->id)
            ->where('status', 1)
            ->where('platform_code', 'alipay')
            ->with([
                'sp'      => function ($query) {
                    $query->field(['name']);
                },
                'entries' => function ($query) {
                    $query->field(['id', 'supply']);
                },
            ])
            ->field(['id', 'name', 'platform_code'])
            ->order('id', 'desc')
            ->paginate($limit);
        $this->success('ok', $list);
    }


}

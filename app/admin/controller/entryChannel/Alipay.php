<?php

// 还原自 Swoole Compiler 加密文件: Alipay.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/entryChannel/Alipay.php

namespace app\admin\controller\entryChannel;

class Alipay extends \app\common\controller\Backend {
    public function getmchappauthtokenurl(): void
    {
        $id = request()->param('id');
        if (empty($id)) {
            $this->error('进件记录ID为空');
        }
        $entry = \app\admin\model\merchant\MerchantEntry::find($id);
        if (empty($entry)) {
            $this->error('进件记录不存在');
        }
        try {
            $service = new \app\common\library\entryChannel\Alipay($entry->channel);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('ok', $service->getAppAuthTokenUrl($entry));
    }

    public function getcapability(): void
    {
        $id = request()->param('id');
        return;
    }

}

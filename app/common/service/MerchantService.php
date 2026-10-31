<?php

// 还原自 Swoole Compiler 加密文件: MerchantService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/MerchantService.php

namespace app\common\service;

class MerchantService{
    public function setmerchantconfig(\app\admin\model\merchant\Merchant|null $merchant = null): void
    {
        // [opcode 对照] switch(platform_code) case 'wxpay': 同步进件通道 api_types 到商户 config
        switch ($merchant->platform_code) {
            case 'wxpay':
                $ec = \app\admin\model\EntryChannel::find($merchant->ec_id);
                if (empty($ec)) {
                    throw new \ba\Exception('进件通道不存在');
                }
                if (empty($ec->config['api_types'])) {
                    return;
                }
                $config = $merchant->config ?? [];
                $config['api_types'] = $ec->config['api_types'];
                $merchant->config = $config;
                $merchant->save();
                break;
        }
    }

}

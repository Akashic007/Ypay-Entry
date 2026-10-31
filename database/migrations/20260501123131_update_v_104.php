<?php

use app\admin\model\channel\Type;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV104 extends Migrator
{
    public function change()
    {
        $this->upPayChannel();
        $this->upChannelPool();
        Db::name('config')->where('name', 'version')->update(['value' => 'V1.0.4']);
    }

    // 支付通道修改
    private function upPayChannel():void
    {
        $channel = Type::where('type','wxpay_scan')->find();
        $channel->save([
            'pay_type' => 'wxpay'
        ]);
    }

    // 修复通道轮询池排序字段错误问题
    private function upChannelPool():void
    {
        $pool = $this->table('channel_pool');
        if ($pool->hasColumn('weight')){
            // 改成weigh
            $pool->renameColumn('weight', 'weigh')->update();
        }

        $pool_account = $this->table('channel_pool_account');
        if ($pool_account->hasColumn('weight')){
            // 改成weigh
            $pool_account->renameColumn('weight', 'weigh')->update();
        }


    }
}

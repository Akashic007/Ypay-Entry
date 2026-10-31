<?php

use app\admin\model\channel\Account;
use app\admin\model\channel\Type;
use think\facade\Db;
use think\migration\Migrator;
use think\migration\db\Column;

class UpdateV105 extends Migrator
{
    public function change():void
    {
        $this->upChannelAccount();
        $this->upPayChannel();
        $this->addConfig();
        $this->upConfig();
        Db::name('config')->where('name', 'version')->update(['value' => 'V1.0.5']);

    }


    private function upChannelAccount():void
    {
        // 修复微信官方扫码通道出现支付通道类型错误问题
        Account::where('channel_type','wxpay_scan')
            ->update(['pay_type' => 'wxpay']);
    }

    private function addConfig():void
    {
        $home_menu = Db::name('config')->where('name', 'home_menu')->find();
        if (!$home_menu) {
            Db::name('config')->insert([
                'name' => 'home_menu',
                'group' => 'basics',
                'title' => '首页自定义导航栏',
                'tip' => '',
                'type' => 'array',
                'value' => '[{"key":"\u9996\u9875","value":"\/"},{"key":"\u5bf9\u63a5\u6587\u6863","value":"\/doc"},{"key":"\u652f\u4ed8\u6d4b\u8bd5","value":"\/demo"}]',
                'content' => '',
                'rule' => '',
                'allow_del' => 0,
                'extend' => '{"baInputExtend":{"keyTitle":"\u6807\u9898","valueTitle":"URL\u5730\u5740"}}',
                'weigh' => 0,
            ]);
        }

        $smtp_sender_name = Db::name('config')->where('name', 'smtp_sender_name')->find();
        if (!$smtp_sender_name) {
            Db::name('config')->insert([
                'name' => 'smtp_sender_name',
                'group' => 'mail',
                'title' => 'SMTP发件人名称',
                'tip' => '',
                'type' => 'string',
                'value' => '',
                'content' => '',
                'rule' => '',
                'allow_del' => 0,
                'extend' => '',
                'weigh' => 0,
            ]);
        }
    }

    // 支付通道修改
    private function upPayChannel():void
    {
        $channel = Type::where('type','wxpay_scan')->find();
        $channel->save([
            'pay_type' => 'wxpay'
        ]);
    }

    private function upConfig():void
    {
        $config = \app\admin\model\Config::where('name','home_menu')->find();
        if ($config){
            $config->save([
                'group' => 'basics'
            ]);
        }
    }
}

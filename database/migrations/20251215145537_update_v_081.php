<?php

use think\facade\Db;
use think\migration\Migrator;

class UpdateV081 extends Migrator
{
    public function change()
    {

        // 删掉注册验证码
        $reg = Db::name('config')->where('name', 'is_reg_captcha')->find();
        if ($reg) {
            Db::name('config')->where('name', 'is_reg_captcha')->delete();
        }

        // 合并到一起
        $login = Db::name('config')->where('name', 'is_login_captcha')->find();
        if ($login) {
            Db::name('config')->where('name', 'is_login_captcha')->update([
                'name' => 'is_login_reg_captcha',
                'title' => '登录|注册验证码'
            ]);
        }

        // 新增会员补单配置
        if (Db::name('config')->where('name', 'user_manual')->count() == 0){
            Db::name('config')->insert([
                'name' => 'user_manual',
                'group' => 'member',
                'title' => '会员补单',
                'type' => 'radio',
                'value' => 'close',
                'content' => '{"close":"\u5173\u95ed","open":"\u5f00\u542f"}',
                'extend' => '{"baInputExtend":{"border":true}}',
                'allow_del'=>0,
                'weigh'=>0
            ]);
        }
        Db::name('config')->where('name', 'version')->update(['value' => 'V0.8.1']);
    }
}

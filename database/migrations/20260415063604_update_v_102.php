<?php

use app\admin\model\Config;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV102 extends Migrator
{

    /**
     * @return void
     * @throws Exception
     */
    public function change():void
    {
        $this->upConfig();
        Db::name('config')->where('name', 'version')->update(['value' => 'V1.0.2']);
    }


    /**
     * 新增配置项
     * @return void
     * @throws Exception
     */
    public function upConfig():void
    {
        // 配置分组更新 - 新增验证码配置
        Config::where('name','config_group')->save([
            'value' => '[{"key":"basics","value":"Basics"},{"key":"login_reg","value":"\u767b\u5f55|\u6ce8\u518c"},{"key":"member","value":"\u4f1a\u5458\u914d\u7f6e"},{"key":"security","value":"\u5b89\u5168\u914d\u7f6e"},{"key":"extend","value":"\u529f\u80fd\u589e\u5f3a"},{"key":"payment","value":"\u652f\u4ed8\u914d\u7f6e"},{"key":"captcha","value":"\u9a8c\u8bc1\u7801\u914d\u7f6e"}]'
        ]);

        // 新增配置项 - 验证码配置
        $captcha_config = Db::name('config')->where('name','captcha_config')->find();
        if (!$captcha_config) {
            Db::name('config')->insert([
                'name' => 'captcha_config',
                'group' => 'captcha',
                'title' => '验证码配置',
                'tip' => '',
                'type' => 'captchaConfig',
                'value' => '{"type":"img"}',
                'content' => '{"close":"\u5173\u95ed","open":"\u5f00\u542f"}',
                'rule' => '',
                'allow_del' => 0,
                'extend' => '',
                'weigh' => 0,
            ]);
        }
    }

}

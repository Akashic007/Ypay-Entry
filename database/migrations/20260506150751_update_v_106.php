<?php

use app\admin\model\user\Config;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV106 extends Migrator
{
    public function change()
    {
        $this->upUserConfig();
        Db::name('config')->where('name', 'version')->update(['value' => 'V1.0.6']);
    }


    /**
     * 用户配置更新
     * @return void
     */
    private function upUserConfig():void
    {
        $configList = Config::where('id', '>', 0)->select();
        foreach ($configList as $config) {
            $channel_config = $config->channel_config;
            $channel_config['is_site_name_params'] = 'open';
            $config->channel_config = $channel_config;
            $config->save();
        }
    }
}

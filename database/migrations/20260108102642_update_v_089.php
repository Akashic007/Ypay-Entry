<?php

use app\admin\model\EntryChannel;
use app\admin\model\ServiceProvider;
use think\migration\Migrator;
use think\migration\db\Column;
use think\facade\Db;

class UpdateV089 extends Migrator
{
    public function change(): void
    {
        $this->entryChannelHandle();
        $this->userRule();
        Db::name('config')->where('name', 'version')->update(['value' => 'V0.8.9']);
    }

    /**
     * 服务商和进件通道数据一致性处理
     */
    private function entryChannelHandle(): void
    {
        $channels = EntryChannel::select();
        // 循环删除服务商已经不存在的进件通道
        foreach ($channels as $channel) {
            $exist = ServiceProvider::where('id', $channel->sp_id)->value('id');
            if (!$exist) {
                $channel->delete();
            }
        }
    }


    private function userRule(): void
    {
        // 用户菜单表 type字段改成varchar类型
        $table = $this->table('user_rule');
        if ($table->hasColumn('type')) {
            $table->changeColumn('type', 'string', ['limit' => 100, 'null' => false, 'default' => '', 'comment' => '类型:route=路由,menu_dir=菜单目录,menu=菜单项,nav_user_menu=顶栏会员菜单下拉项,nav=顶栏菜单项,button=页面按钮']);
            $table->update();
        }
    }
}

<?php

use think\facade\Db;
use think\migration\Migrator;
use think\migration\db\Column;

class UpdateV084 extends Migrator
{
    public function change()
    {
        Db::name('admin_rule')->where('type','menu')->update(['keepalive'=>0]);
        Db::name('admin_rule')->where('name','user/user')->update(['title'=>'会员列表']);
        Db::name('config')->where('name', 'version')->update(['value' => 'V0.8.4']);
    }
}

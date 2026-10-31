<?php

use think\facade\Db;
use think\migration\Migrator;
use think\migration\db\Column;

class UpdateV082 extends Migrator
{
    public function change()
    {
        Db::name('config')->where('name', 'version')->update(['value' => 'V0.8.2']);
    }
}

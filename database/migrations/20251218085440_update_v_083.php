<?php

use think\facade\Db;
use think\migration\Migrator;

class UpdateV083 extends Migrator
{

    public function change()
    {
        Db::name('config')->where('name', 'version')->update(['value' => 'V0.8.3']);
    }
}

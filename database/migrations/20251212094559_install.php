<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Install extends Migrator
{
    public function change()
    {
        $sqlFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'install.sql';

        if (!file_exists($sqlFile)) {
            throw new \RuntimeException('install.sql 不存在，无法继续安装');
        }

        $sql = file_get_contents($sqlFile);
        if ($sql === false || trim($sql) === '') {
            throw new \RuntimeException('install.sql 内容为空，无法继续安装');
        }

        // 使用原生 PDO 执行完整 SQL 脚本，确保多语句依次运行
        $pdo = $this->getAdapter()->getConnection();
        $pdo->exec($sql);
    }
}

<?php

// 还原自 Swoole Compiler 加密文件: Fuiou.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/entryNotify/Fuiou.php
//
// [2026-09-13 opcode 全量对照还原] 富友签约回调：
//   request()->param() → file_put_contents(runtime_path().'fuiouSign.txt', json_encode($params)) 入站日志 → echo 1; exit

namespace app\api\controller\entryNotify;

class Fuiou extends \app\common\controller\Frontend
{
    protected array $noNeedLogin = ['*'];

    public function signContract(): void
    {
        $params = request()->param() ?? [];

        // 入站日志（原始 opcode: file_put_contents(runtime_path() . 'fuiouSign.txt', json_encode($params))）
        file_put_contents(runtime_path() . 'fuiouSign.txt', json_encode($params));

        echo 1;
        exit;
    }
}

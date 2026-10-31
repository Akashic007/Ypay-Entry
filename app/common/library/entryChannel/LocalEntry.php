<?php

// 还原自 Swoole Compiler 加密文件: LocalEntry.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/entryChannel/LocalEntry.php

namespace app\common\library\entryChannel;

abstract class LocalEntry extends \app\common\library\entryChannel\AbstractEntry {
    protected array $config;
    public function __construct(\app\admin\model\EntryChannel $channel)
    {
        parent::__construct($channel);
        if ($channel->source != 'local') {
            throw new \ba\Exception('通道初始化错误：不能将上游通道实例化为本地通道');
        }
        if (empty($channel->config)) {
            throw new \ba\Exception('通道配置不存在');
        }
        if (!is_array($channel->config)) {
            throw new \ba\Exception('通道配置格式错误');
        }
        $this->config = $channel->config;
    }

}

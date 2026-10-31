<?php

// 还原自 Swoole Compiler 加密文件: EntryChannel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/upstream/EntryChannel.php

namespace app\common\library\upstream;

class EntryChannel{
    protected \app\admin\model\supply\Upstream $up;
    protected \app\common\library\upstream\UpRequest $ur;
    public function __construct(\app\common\library\upstream\UpRequest $ur)
    {
        $this->ur = $ur;
        if (empty($ur->upstream)) {
            throw new \ba\Exception('上游不存在');
        }
        $this->up = $ur->upstream;
    }


    public function getchannellist(): array
    {
        try {
            $this->ur->setUrl('/supply/Entry/getChannelList');
            $res = $this->ur->httpUrl('GET');
        } catch (\Exception $e) {
            throw new \ba\Exception($e->getMessage());
        }
        if ($res['code'] != 1) {
            throw new \ba\Exception($res['msg']);
        }
        return $res['data'];
    }


    public function getchannel(string|null $channel_id = null): array
    {
        try {
            $this->ur->setUrl('/supply/Entry/getChannelInfo');
            $this->ur->setParams(['ec_id' => $channel_id]);
            $res = $this->ur->httpUrl('GET');
        } catch (\Exception $e) {
            throw new \ba\Exception($e->getMessage());
        }
        if ($res['code'] != 1) {
            throw new \ba\Exception($res['msg']);
        }
        if (!\app\common\library\SignUtil::verifySign($res['data'], $this->up->api_key)) {
            throw new \ba\Exception('签名校验错误');
        }
        return $res['data'];
    }


}

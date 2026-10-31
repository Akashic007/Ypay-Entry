<?php

// 还原自 Swoole Compiler 加密文件: UpRequest.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/upstream/UpRequest.php

namespace app\common\library\upstream;

class UpRequest{
    public \app\admin\model\supply\Upstream $upstream;
    public string $sign;
    public array $params = [];
    public string $url = '';
    public array $noSignParams = [];
    public function __construct(\app\admin\model\supply\Upstream $upstream, array $params = [])
    {
        $this->upstream = $upstream;
        $this->params = $params;
    }


    public function setparams(array|null $params = null): self
    {
        $this->params = $params;
        return $this;
    }


    public function seturl(string|null $url = null): self
    {
        $this->url = $url;
        return $this;
    }


    public function httpurl(string|null $method = 'POST'): array
    {
        if (empty($this->url)) {
            throw new \Exception('请求URL为空');
        }
        $this->buildSign();
        $res = httpCurl($this->upstream->api . $this->url, $method, $this->params, ['server:1'], true);
        if ($res['status'] !== 200) {
            throw new \Exception('请求异常' . $res['status'] . '：' . $res['error']);
        }
        if (empty($res['body'])) {
            throw new \Exception('请求结果异常，请检查配置后重试');
        }
        if (is_array($res['body'])) {
            return $res['body'];
        }
        return json_decode($res['body'], true);
    }


    private function buildsign(): void
    {
        $params = $this->params;
        $params['api_id'] = $this->upstream->api_id;
        $params = array_filter($params, function ($value) {
            return !$value instanceof \CURLFile && !is_array($value) && !is_object($value);
        });
        $sign = \app\common\library\SignUtil::generateSign($params, $this->upstream->api_key);
        $this->params['api_id'] = $this->upstream->api_id;
        $this->params['sign'] = $sign;
    }


}

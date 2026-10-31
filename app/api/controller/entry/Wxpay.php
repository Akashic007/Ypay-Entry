<?php

// 还原自 Swoole Compiler 加密文件: Wxpay.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/entry/Wxpay.php

namespace app\api\controller\entry;

class Wxpay extends \app\common\controller\Frontend {
    protected \app\common\service\entry\contract\WxpayOfficial $channel;
    public function initialize(): void
    {
    parent::initialize();
    try {
        $param = $this->request->param();
        if (empty($param['ec_id'])) {
            $this->error('通道ID为空');
        }
        $entryChannel = \app\admin\model\EntryChannel::find($param['ec_id']);
        if (empty($entryChannel)) {
            $this->error('进件通道不存在');
        }
        $this->channel = \app\common\service\entry\EntryFactory::create($entryChannel);
    } catch (\Exception $e) {
        $this->error($e->getMessage());
    }
    }

    public function getbanklist(): void
    {
    // [opcode 对照] param 白名单槽1为受保护串(清零)，依用法取 'keyword'；limit 恒走默认 10（与 getbranchbanklist 写法一致）
    $params = $this->request->param(['type', 'keyword', 'page']);
    if (empty($params['type'])) {
        $params['type'] = 'BANK_ACCOUNT_TYPE_PERSONAL';
    }
    if (empty($params['page'])) {
        $params['page'] = 1;
    }
    if (empty($params['limit'])) {
        $params['limit'] = 10;
    }
    if (empty($params['keyword'])) {
        $params['keyword'] = '';
    }
    try {
        $result = $this->channel->queryBankList($params['type'], $params['page'], $params['limit'], $params['keyword']);
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
        return;
    }
    $this->success('ok', $result);
    }

    public function getbranchbanklist(): void
    {
    // [opcode 对照] param 白名单槽1为受保护串(清零)，依用法取 'city_code'（否则本接口恒报 参数错误）
    $params = $this->request->param(['page', 'city_code', 'bank_alias_code']);
    $keyword = (string)$this->request->param('keyword', '');
    if (empty($params['page'])) {
        $params['page'] = 1;
    }
    if (empty($params['limit'])) {
        $params['limit'] = 10;
    }
    if (empty($params['bank_alias_code']) || empty($params['city_code'])) {
        $this->error('参数错误');
    }
    try {
        $result = $this->channel->queryBankBranchList($params['bank_alias_code'], $params['city_code'], $params['page'], $params['limit'], $keyword);
    } catch (\Exception $e) {
        $this->error($e->getMessage());
        return;
    }
    $this->success('ok', $result);
    }

    public function getprovincelist(): void
    {
    try {
        $result = $this->channel->queryProvinceList();
    } catch (\Exception $e) {
        $this->error($e->getMessage());
        return;
    }
    $this->success('ok', $result);
    }

    public function getcitylist(): void
    {
    $provinceCode = $this->request->param('province_code');
    if (empty($provinceCode)) {
        $this->error('省份编码为空');
    }
    try {
        $result = $this->channel->queryCityList($provinceCode);
    } catch (\Exception $e) {
        $this->error($e->getMessage());
        return;
    }
    $this->success('ok', $result);
    }

}

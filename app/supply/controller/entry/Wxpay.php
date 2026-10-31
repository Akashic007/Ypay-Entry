<?php

// 还原自 Swoole Compiler 加密文件: Wxpay.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/supply/controller/entry/Wxpay.php

namespace app\supply\controller\entry;

class Wxpay extends \app\common\controller\SupplyBase {
    protected \app\admin\model\EntryChannel $channel;
    protected \app\common\library\entryChannel\WxpayEntry $we;
    public function initialize(): void
    {
        parent::initialize();
        $ecId = $this->request->param('ec_id');
        $channel = \app\admin\model\EntryChannel::where('id', $ecId)->find();
        if (empty($channel)) {
            $this->error('上游进件渠道不存在');
        }
        $this->channel = $channel;
        $this->we = new \app\common\library\entryChannel\WxpayEntry($channel);
    }

    public function getbanklist(): void
    {
    // [opcode 对照] param 白名单槽1/槽3为受保护串(清零)，依用法取 'type'/'keyword'
    $param = $this->request->param(['page', 'type', 'limit', 'keyword']);
    if (empty($param['page']) || empty($param['limit'])) {
        $this->error('分页参数为空');
    }
    try {
        $type = $param['type'] ?? 'BANK_ACCOUNT_TYPE_PERSONAL';
        $list = $this->we->queryBankList($type, $param['page'], $param['limit'], $param['keyword']);
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
        return;
    }
    $this->success('ok', [
        'list' => $list['list'],
        'total' => $list['total'],
    ]);
    }

    public function getbankbranchlist(): void
    {
    // [opcode 对照] param 白名单槽1/槽3为受保护串(清零)，依用法取 'bank_alias_code'/'city_code'
    $param = $this->request->param(['page', 'bank_alias_code', 'limit', 'city_code', 'keyword']);
    if (empty($param['bank_alias_code']) || empty($param['city_code'])) {
        $this->error('参数错误');
    }
    if (empty($param['page']) || empty($param['limit'])) {
        $this->error('分页参数为空');
    }
    try {
        $list = $this->we->queryBankBranchList($param['bank_alias_code'], $param['city_code'], $param['page'], 10, $param['keyword']);
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
        return;
    }
    $this->success('ok', [
        'list' => $list['data'],
        'total' => $list['total_count'],
        'offset' => $list['offset'],
    ]);
    }

    public function getprovincelist(): void
    {
    try {
        $list = $this->we->queryProvinceList();
    } catch (\Exception $e) {
        $this->error($e->getMessage());
        return;
    }
    $this->success('ok', [
        'list' => $list['data'],
        'total' => $list['total_count'],
    ]);
    }

    public function getcitylist(): void
    {
    $provinceCode = $this->request->param('province_code');
    if (empty($provinceCode)) {
        $this->error('省份编码为空');
    }
    try {
        $list = $this->we->queryCityList($provinceCode);
    } catch (\Exception $e) {
        $this->error($e->getMessage());
        return;
    }
    $this->success('ok', [
        'list' => $list['data'],
        'total' => $list['total_count'],
    ]);
    }

}

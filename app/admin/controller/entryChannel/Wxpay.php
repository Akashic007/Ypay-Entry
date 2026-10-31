<?php

// 还原自 Swoole Compiler 加密文件: Wxpay.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/entryChannel/Wxpay.php

namespace app\admin\controller\entryChannel;

class Wxpay extends \app\common\controller\Backend {
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
    // [opcode 对照] param 白名单 3 槽中槽1为受保护串(编译器清零)，依用法取 'quickSearch'（同 B40 api 端取 'keyword' 先例）
    $params = $this->request->param(['type', 'quickSearch', 'page']);
    if (empty($params['type'])) {
        $params['type'] = 'BANK_ACCOUNT_TYPE_PERSONAL';
    }
    if (empty($params['page'])) {
        $params['page'] = 1;
    }
    if (empty($params['limit'])) {
        $params['limit'] = 10;
    }
    if (empty($params['quickSearch'])) {
        $params['quickSearch'] = '';
    }
    try {
        $result = $this->channel->queryBankList($params['type'], $params['page'], $params['limit'], $params['quickSearch']);
    } catch (\Throwable $e) {
        $this->error($e->getMessage());
        return;
    }
    $this->success('ok', $result);
    }

    public function getbranchbanklist(): void
    {
    // [opcode 对照] param 白名单 3 槽中槽1为受保护串(编译器清零)，依用法取 'city_code'（否则本接口恒报 参数错误；同 api 端）
    $params = $this->request->param(['page', 'city_code', 'bank_alias_code']);
    $keyword = (string)$this->request->param('quickSearch', '');
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
    $this->success('ok', ['list' => $result]);
    }

    public function getprovincelist(): void
    {
    try {
        $result = $this->channel->queryProvinceList();
    } catch (\Exception $e) {
        $this->error($e->getMessage());
        return;
    }
    $this->success('ok', ['list' => $result['data']]);
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
    $this->success('ok', ['list' => $result['data']]);
    }

    public function getsettlement(): void
    {
    $id = $this->request->param('id');
    if (empty($id)) {
        $this->error('ID为空');
    }
    $entry = \app\admin\model\merchant\MerchantEntry::with('merchant')->find($id);
    if (empty($entry)) {
        $this->error('进件记录不存在');
    }
    if ($entry->status != 8) {
        $this->error('进件记录未完成');
    }
    if (empty($entry->merchant)) {
        $this->error('进件商户资料为空');
    }
    $merchant = $entry->merchant;
    if (empty($merchant->merchant_info['settlement'])) {
        if (empty($merchant->merchant_id)) {
            $this->error('商户ID为空');
        }
        $channel = \app\admin\model\EntryChannel::where('status', 1)->find($entry->ec_id);
        if (empty($channel)) {
            $this->error('进件渠道不存在');
        }
        $entryService = new \app\common\library\entryChannel\WxpayEntry($channel);
        try {
            $settlementInfo = $entryService->selectSettlement($merchant->merchant_id);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $merchantInfo = $merchant->merchant_info;
        $merchantInfo['settlement'] = $settlementInfo;
        $merchant->merchant_info = $merchantInfo;
        $merchant->save();
    }
    $this->success('ok', $merchant->merchant_info['settlement']);
    }

    public function modifysettlement(): void
    {
    if (!$this->request->isPost()) {
        $this->error('错误的请求方式');
    }
    $id = $this->request->post('id');
    if (empty($id)) {
        $this->error('ID为空');
    }
    $entry = \app\admin\model\merchant\MerchantEntry::with('merchant')->find($id);
    if (empty($entry)) {
        $this->error('进件记录不存在');
    }
    if ($entry->status != 8) {
        $this->error('进件记录未完成');
    }
    if (empty($entry->merchant)) {
        $this->error('进件商户资料为空');
    }
    $merchant = $entry->merchant;
    // [opcode 对照] post 白名单 6 槽中 3 个受保护串被编译器清零(奇数槽)，真实键集由本方法后续 dim 读取(account_type/account_bank/bank_name/bank_branch_id/account_number)
    // 与 admin 前端 modifySettlementDialog 提交体(含 account_name)共同实证；顺序按前端字段序近似
    $post = $this->request->post(['account_type', 'account_name', 'account_bank', 'bank_branch_id', 'bank_name', 'account_number']);
    if (empty($post['account_type'])) {
        $this->error('银行账户类型为空');
    }
    if (empty($post['account_bank'])) {
        $this->error('开户银行为空');
    }
    if (empty($post['bank_name']) && empty($post['bank_branch_id'])) {
        $this->error('开户银行名称或联行号不能同时为空');
    }
    if (empty($post['account_number'])) {
        $this->error('银行账号为空');
    }
    $channel = \app\admin\model\EntryChannel::where('status', 1)->find($entry->ec_id);
    if (empty($channel)) {
        $this->error('进件渠道不存在');
    }
    $entryService = new \app\common\library\entryChannel\WxpayEntry($channel);
    try {
        $result = $entryService->modifySettlement($merchant, $post);
    } catch (\Exception $e) {
        $this->error($e->getMessage());
        return;
    }
    if (!empty($result['application_no'])) {
        $merchantInfo = $merchant->merchant_info;
        $merchantInfo['application_no'] = $result['application_no'];
        $merchant->merchant_info = $merchantInfo;
        $merchant->save();
    }
    $this->success('修改银行账户申请成功', ['application_no' => $result['application_no'] ?? '']);
    }

    public function getmodifysettlementinfo(): void
    {
    $id = $this->request->param('id');
    if (empty($id)) {
        $this->error('ID为空');
    }
    $entry = \app\admin\model\merchant\MerchantEntry::with('merchant')->find($id);
    if (empty($entry)) {
        $this->error('进件记录不存在');
    }
    if ($entry->status != 8) {
        $this->error('商户未完成进件');
    }
    if (empty($entry->merchant)) {
        $this->error('商户不存在');
    }
    $merchant = $entry->merchant;
    if (empty($merchant->merchant_info['application_no'])) {
        $this->error('商户未申请修改结算信息');
    }
    $channel = \app\admin\model\EntryChannel::where('status', 1)->find($entry->ec_id);
    if (empty($channel)) {
        $this->error('进件渠道不存在');
    }
    $entryService = new \app\common\library\entryChannel\WxpayEntry($channel);
    try {
        $verifyInfo = $entryService->selectModifySettlementInfo($merchant);
    } catch (\Exception $e) {
        $this->error($e->getMessage());
        return;
    }
    if (isset($verifyInfo['verify_result']) && $verifyInfo['verify_result'] == 'AUDIT_SUCCESS') {
        $merchantInfo = $merchant->merchant_info;
        $merchantInfo['settlement'] = $verifyInfo;
        unset($merchantInfo['application_no']);
        $merchant->merchant_info = $merchantInfo;
        $merchant->save();
    }
    $this->success('ok', $verifyInfo);
    }

}

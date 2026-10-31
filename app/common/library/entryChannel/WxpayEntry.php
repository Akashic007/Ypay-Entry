<?php

// 还原自 Swoole Compiler 加密文件: WxpayEntry.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/entryChannel/WxpayEntry.php

namespace app\common\library\entryChannel;

class WxpayEntry extends \app\common\library\entryChannel\LocalEntry implements \app\common\service\entry\contract\WxpayOfficial {
    protected string $platformCertificateSerial;
    protected \OpenSSLAsymmetricKey $platformCertificateInstance;
    private string $appId;
    protected object $instance;
    const BANK_CACHE_TTL = 86400;
    const BANK_FETCH_LIMIT = 200;

    public function __construct(\app\admin\model\EntryChannel $channel)
    {
        parent::__construct($channel);

        $this->appId = $channel->config['appId'];

        try {
            $requestTool                       = new \app\common\library\wechat\RequestTool($channel->config);
            $this->instance                    = $requestTool->getInstance();
            $this->platformCertificateSerial   = $this->instance->getPlatformCertSerial();
            $this->platformCertificateInstance = \WeChatPay\Crypto\Rsa::from(file_get_contents($this->instance->getPlatformCertPath()), \WeChatPay\Crypto\Rsa::KEY_TYPE_PUBLIC);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function apply(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): void
    {
        if (!in_array($mchEntry->status, ['0', '1'])) {
            throw new \Exception('当前状态无需再次提交资料');
        }

        $applyData = $mchEntry->apply_data;

        try {
            validate(\app\common\validate\WxpayMerchantValidate::class)->check($applyData);
        } catch (\think\exception\ValidateException $e) {
            throw new \Exception($e->getMessage());
        }

        $applyData['contact_info']['contact_name']      = $this->encryptor($applyData['contact_info']['contact_name'] ?? '');
        $applyData['contact_info']['contact_id_number'] = $this->encryptor($applyData['contact_info']['contact_id_number'] ?? '');
        $applyData['contact_info']['mobile_phone']      = $this->encryptor($applyData['contact_info']['mobile_phone'] ?? '');
        $applyData['contact_info']['contact_email']     = $this->encryptor($applyData['contact_info']['contact_email'] ?? '');

        if (isset($applyData['subject_info']['identity_info']['id_card_info'])) {
            $applyData['subject_info']['identity_info']['id_card_info']['id_card_name']    = $this->encryptor($applyData['subject_info']['identity_info']['id_card_info']['id_card_name'] ?? '');
            $applyData['subject_info']['identity_info']['id_card_info']['id_card_number']  = $this->encryptor($applyData['subject_info']['identity_info']['id_card_info']['id_card_number'] ?? '');
            $applyData['subject_info']['identity_info']['id_card_info']['id_card_address'] = $this->encryptor($applyData['subject_info']['identity_info']['id_card_info']['id_card_address'] ?? '');
        }

        if (isset($applyData['subject_info']['identity_info']['id_doc_info'])) {
            $applyData['subject_info']['identity_info']['id_doc_info']['id_doc_name']    = $this->encryptor($applyData['subject_info']['identity_info']['id_doc_info']['id_doc_name'] ?? '');
            $applyData['subject_info']['identity_info']['id_doc_info']['id_doc_number']  = $this->encryptor($applyData['subject_info']['identity_info']['id_doc_info']['id_doc_number'] ?? '');
            $applyData['subject_info']['identity_info']['id_doc_info']['id_doc_address'] = $this->encryptor($applyData['subject_info']['identity_info']['id_doc_info']['id_doc_address'] ?? '');
        }

        $applyData['bank_account_info']['account_name']   = $this->encryptor($applyData['bank_account_info']['account_name'] ?? '');
        $applyData['bank_account_info']['account_number'] = $this->encryptor($applyData['bank_account_info']['account_number'] ?? '');

        if (isset($applyData['subject_info']['ubo_info_list']) && is_array($applyData['subject_info']['ubo_info_list'])) {
            foreach ($applyData['subject_info']['ubo_info_list'] as &$item) {
                if (!is_array($item)) {
                    continue;
                }
                $item['ubo_name']         = $this->encryptor($item['ubo_name'] ?? '');
                $item['ubo_id_number']    = $this->encryptor($item['ubo_id_number'] ?? '');
                $item['ubo_mobile_phone'] = $this->encryptor($item['ubo_mobile_phone'] ?? '');
                $item['ubo_email']        = $this->encryptor($item['ubo_email'] ?? '');
            }
            unset($item);
        }

        $applyData = $this->mediaBuild($applyData);

        $applyData['business_code'] = $mchEntry->business_id;

        if (isset($applyData['subject_info']['finance_institution']) && $applyData['subject_info']['finance_institution'] == 'true') {
            $applyData['subject_info']['finance_institution'] = true;
        } else {
            unset($applyData['subject_info']['finance_institution']);
        }

        if (!in_array($applyData['subject_info']['subject_type'], ['SUBJECT_TYPE_GOVERNMENT', 'SUBJECT_TYPE_INSTITUTIONS'])) {
            unset($applyData['subject_info']['certificate_info']);
        }

        if (empty($applyData['subject_info']['certificate_info'])) {
            unset($applyData['subject_info']['certificate_info']);
        }

        if ($applyData['subject_info']['identity_info']['id_doc_type'] == 'IDENTIFICATION_TYPE_IDCARD') {
            unset($applyData['subject_info']['identity_info']['id_doc_info']);
        } else {
            unset($applyData['subject_info']['identity_info']['id_card_info']);
        }

        if (empty($applyData['settlement_info']['activities_id'])) {
            unset($applyData['settlement_info']['activities_id']);
            unset($applyData['settlement_info']['credit_activities_rate']);
            unset($applyData['settlement_info']['debit_activities_rate']);
        }

        if (empty($applyData['bank_account_info']['account_bank'])) {
            throw new \Exception('开户银行为空');
        }

        if (is_array($applyData['bank_account_info']['account_bank'])) {
            $accountBank = $applyData['bank_account_info']['account_bank'];
            $applyData['bank_account_info']['account_bank'] = $accountBank['account_bank'];
        }

        if (in_array('SALES_SCENES_MP', $applyData['business_info']['sales_info']['sales_scenes_type'])) {
            if (empty($applyData['business_info']['sales_info']['mp_info']['mp_appid']) && empty($applyData['business_info']['sales_info']['mp_info']['mp_sub_appid'])) {
                $applyData['business_info']['sales_info']['mp_info']['mp_appid'] = $this->appId;
            }
        }

        try {
            $res = $this->instance->chain('/v3/applyment4sub/applyment/')->post([
                'json' => [
                    'business_code'     => $applyData['business_code'],
                    'contact_info'      => $applyData['contact_info'],
                    'business_info'     => $applyData['business_info'],
                    'subject_info'      => $applyData['subject_info'],
                    'bank_account_info' => $applyData['bank_account_info'],
                    'settlement_info'   => $applyData['settlement_info'],
                ],
                'headers' => [
                    'Accept'           => 'application/json',
                    'Wechatpay-Serial' => $this->platformCertificateSerial,
                    'Content-Type'     => 'application/json',
                ],
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data     = json_decode($response->getBody()->getContents(), true);
                $msg      = $data['message'];
            } else {
                $msg = $e->getMessage();
            }
            $mchEntry->status       = 0;
            $mchEntry->audit_result = $msg;
            $mchEntry->save();
            throw new \Exception($msg);
        }

        $data = json_decode($res->getBody()->getContents(), true);
        if (empty($data['applyment_id'])) {
            $mchEntry->status       = 0;
            $mchEntry->audit_result = '微信支付申请单号不存在';
            $mchEntry->save();
            return;
        }
        $mchEntry->apply_id = $data['applyment_id'];
        $mchEntry->status   = 1;
        $mchEntry->save();
        $this->getApplyInfo($mchEntry);
    }

    public function getApplyInfo(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): array
    {
        if ($mchEntry->status == '0') {
            throw new \Exception('当前资料暂未提交');
        }

        if (empty($mchEntry->apply_id)) {
            $path = '/v3/applyment4sub/applyment/business_code/' . $mchEntry->business_id;
        } else {
            $path = '/v3/applyment4sub/applyment/applyment_id/' . $mchEntry->apply_id;
        }

        if ($mchEntry->status == 8) {
            return [
                'status'       => $mchEntry->status,
                'status_text'  => $mchEntry->statusText,
                'business_id'  => $mchEntry->business_id,
                'audit_result' => $mchEntry->audit_result,
                'apply_result' => $mchEntry->apply_result,
            ];
        }

        try {
            $res = $this->instance->chain($path)->get();
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data     = json_decode($response->getBody()->getContents());
                $mchEntry->status       = 3;
                $mchEntry->audit_result = $data->message;
                $mchEntry->save();
                throw new \Exception($data->message);
            }
            throw new \Exception($e->getMessage());
        }

        $data = json_decode($res->getBody()->getContents(), true);

        if (empty($data['sign_url'])) {
            $mchEntry->apply_result = [
                'sign_url'        => $data['sign_url'],
                'sign_qrcode_url' => $data['sign_url'],
            ];
        } else {
            $mchEntry->apply_result = $data;
        }

        if (!empty($data['sub_mchid'])) {
            if (!empty($mchEntry->apply_data['subject_info']['business_license_info']['merchant_name'])) {
                $merchantName = $mchEntry->apply_data['subject_info']['business_license_info']['merchant_name'];
            } elseif (!empty($mchEntry->apply_data['subject_info']['certificate_info']['merchant_name'])) {
                $merchantName = $mchEntry->apply_data['subject_info']['certificate_info']['merchant_name'];
            } else {
                $merchantName = $data['business_info']['merchant_shortname'];
            }

            $merchant = \app\admin\model\merchant\Merchant::where([
                'user_id'     => $mchEntry->user_id,
                'merchant_id' => $data['sub_mchid'],
                'ec_id'       => $mchEntry->ec_id,
            ])->find();
            if (empty($merchant)) {
                $channel      = \app\admin\model\EntryChannel::find($mchEntry->ec_id);
                $apiTypes     = $channel->config['api_types'] ?? ['JSAPI'];
                $merchantInfo = [];
                if ($mchEntry->down_id) {
                    $merchantInfo['down_id'] = $mchEntry->down_id;
                }
                if ($mchEntry->apply_id) {
                    $merchantInfo['apply_id'] = $mchEntry->apply_id;
                }
                $merchant = \app\admin\model\merchant\Merchant::create([
                    'user_id'       => $mchEntry->user_id,
                    'sp_id'         => $mchEntry->sp_id,
                    'ec_id'         => $mchEntry->ec_id,
                    'name'          => $merchantName,
                    'platform_code' => 'wxpay',
                    'merchant_id'   => $data['sub_mchid'],
                    'merchant_info' => $merchantInfo,
                    'config'        => [
                        'api_types' => $apiTypes,
                    ],
                    'status'        => 0,
                    'source'        => $mchEntry->source,
                ]);
                \app\admin\model\merchant\EntryMerchant::bindMerchant($mchEntry->id, $merchant->id);
            }
            if ($data['applyment_state'] == 'APPLYMENT_STATE_FINISHED') {
                $merchant->status = 1;
                $merchant->save();
            }
        }

        if (!empty($data['applyment_state'])) {
            $mchEntry->status = match ($data['applyment_state']) {
                'APPLYMENT_STATE_EDITTING', 'APPLYMENT_STATE_AUDITING' => 2,
                'APPLYMENT_STATE_REJECTED'        => 3,
                'APPLYMENT_STATE_TO_BE_CONFIRMED' => 4,
                'APPLYMENT_STATE_TO_BE_SIGNED'    => 5,
                'APPLYMENT_STATE_SIGNING'         => 6,
                'APPLYMENT_STATE_FINISHED'        => 8,
                'APPLYMENT_STATE_CANCELED'        => 7,
            };
        }
        $mchEntry->audit_result = $data['applyment_state_msg'];

        if ($data['applyment_state'] == 'APPLYMENT_STATE_REJECTED' && !empty($data['audit_detail'])) {
            $auditDetail = $data['audit_detail'];
            $str         = '';
            if (is_array($auditDetail) && count($auditDetail) > 0) {
                $i = 1;
                foreach ($auditDetail as $item) {
                    $str .= $i . ':';
                    $str .= !empty($item['field']) ? $item['field'] . '-' : '';
                    $str .= !empty($item['param_name']) ? $item['param_name'] . '-' : '';
                    $str .= !empty($item['reject_reason']) ? $item['reject_reason'] . ';' : '';
                }
            }
            $mchEntry->audit_result = $str;
        }
        $mchEntry->save();

        return [
            'status'       => $mchEntry->status,
            'status_text'  => $mchEntry->statusText,
            'business_id'  => $mchEntry->business_id,
            'audit_result' => $mchEntry->audit_result,
            'apply_result' => $mchEntry->apply_result,
        ];
    }

    public function uploadMedia(\app\common\model\Attachment|null $attachment = null): array
    {
        if (!in_array($attachment->mimetype, ['image/png'])) {
            throw new \Exception('图片格式错误');
        }

        $path      = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $attachment->url;
        $mediaUtil = new \WeChatPay\Util\MediaUtil($path);

        try {
            $res = $this->instance
                ->chain('/v3/merchant/media/upload')
                ->post([
                    'body'    => $mediaUtil->getStream(),
                    'headers' => [
                        'Accept'       => 'application/json',
                        'Content-Type' => $mediaUtil->getContentType(),
                    ],
                ]);
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data     = json_decode($response->getBody()->getContents(), true);
                throw new \Exception($data['message']);
            }
            throw new \Exception($e->getMessage());
        }

        if ($res->getStatusCode() != 200 || $res->getStatusCode() != '200') {
            throw new \Exception('上传结果错误');
        }

        $res     = json_decode($res->getBody()->getContents(), true);
        $mediaId = isset($attachment->media_id) ? [] : $attachment->media_id;
        $mediaId['wxpay'] = trim($res['media_id']);
        $attachment->media_id = $mediaId;
        $attachment->save();

        return $attachment->toArray();
    }

    public function queryBankList(string|null $type = null, int|null $page = null, int|null $limit = null, string|null $keyword = null): array
    {
        $offset = ($page - 1) * $limit;

        if ($type == 'BANK_ACCOUNT_TYPE_PERSONAL') {
            $cacheKey = 'wxpay:banks:personal';
            $banks    = \think\facade\Cache::remember($cacheKey, function () {
                return $this->pullBankListFromApi('personal');
            }, self::BANK_CACHE_TTL);
        } else {
            $cacheKey = 'wxpay:banks:corporate';
            $banks    = \think\facade\Cache::remember($cacheKey, function () {
                return $this->pullBankListFromApi('corporate');
            }, self::BANK_CACHE_TTL);
        }

        $result = $this->paginateBankList($banks, $limit, $offset, $keyword, false);

        return [
            'list'  => $result['data'],
            'total' => $result['total_count'],
        ];
    }

    public function queryBankBranchList(string|null $bank_alias_code = null, string|null $city_code = null, int|null $page = null, int|null $limit = null, string|null $keyword = null): array
    {
        $cacheKey = 'wxpay:banks:branches:' . $bank_alias_code . ':' . $city_code;
        $branches = \think\facade\Cache::remember($cacheKey, function () use ($bank_alias_code, $city_code) {
            return $this->pullBranchListFromApi($bank_alias_code, $city_code);
        }, self::BANK_CACHE_TTL);

        $offset = ($page - 1) * $limit;

        return $this->paginateBankList($branches, $limit, $offset, $keyword, true);
    }

    public function queryProvinceList(): array
    {
        try {
            $res = $this->instance->chain('/v3/capital/capitallhh/areas/provinces')->get();
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data     = json_decode($response->getBody()->getContents(), true);
                throw new \Exception($data['message']);
            }
            throw new \Exception($e->getMessage());
        }

        if ($res->getStatusCode() != 200 || $res->getStatusCode() != '200') {
            throw new \Exception('查询错误');
        }

        return json_decode($res->getBody()->getContents(), true);
    }

    public function queryCityList(string|null $province_code = null): array
    {
        try {
            $res = $this->instance->chain('/v3/capital/capitallhh/areas/provinces/' . $province_code . '/cities')->get();
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data     = json_decode($response->getBody()->getContents(), true);
                throw new \Exception($data['message']);
            }
            throw new \Exception($e->getMessage());
        }

        if ($res->getStatusCode() != 200 || $res->getStatusCode() != '200') {
            throw new \Exception('查询错误');
        }

        return json_decode($res->getBody()->getContents(), true);
    }

    public function getBanksByAccount(string|null $account_number = null): array
    {
        try {
            $res = $this->instance
                ->chain('/v3/capital/capitallhh/banks/search-banks-by-bank-account?account_number')
                ->get([
                    'query'   => [
                        'account_number' => $this->encryptor($account_number),
                    ],
                    'headers' => [
                        'Accept'           => 'application/json',
                        'Wechatpay-Serial' => $this->platformCertificateSerial,
                        'Content-Type'     => 'application/json',
                    ],
                ]);
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data     = json_decode($response->getBody()->getContents(), true);
                throw new \Exception($data['message']);
            }
            throw new \Exception($e->getMessage());
        }

        if ($res->getStatusCode() != 200 || $res->getStatusCode() != '200') {
            throw new \Exception('查询错误');
        }

        return json_decode($res->getBody()->getContents(), true);
    }

    public function selectSettlement(string|null $sub_mchid = null): array
    {
        try {
            $res = $this->instance
                ->chain('/v3/apply4sub/sub_merchants/' . $sub_mchid . '/settlement')
                ->get([
                    'query' => [
                        'account_number_rule' => 'ACCOUNT_NUMBER_RULE_MASK_V2',
                    ],
                ]);
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data     = json_decode($response->getBody()->getContents(), true);
                throw new \Exception($data['message']);
            }
            throw new \Exception($e->getMessage());
        }

        return json_decode($res->getBody()->getContents(), true);
    }

    public function modifySettlement(\app\admin\model\merchant\Merchant|null $merchant = null, array|null $params = null): array
    {
        $params['account_number'] = $this->encryptor($params['account_number']);
        if (!empty($params['account_name'])) {
            $params['account_name'] = $this->encryptor($params['account_name']);
        }

        try {
            $res = $this->instance
                ->chain('/v3/apply4sub/sub_merchants/' . $merchant->merchant_id . '/modify-settlement')
                ->post([
                    'json' => [
                        'account_type'   => $params['account_type'],
                        'account_bank'   => $params['account_bank'],
                        'bank_name'      => $params['bank_name'] ?? '',
                        'bank_branch_id' => $params['bank_branch_id'],
                        'account_number' => $params['account_number'],
                        'account_name'   => $params['account_name'] ?? '',
                    ],
                    'headers' => [
                        'Accept'           => 'application/json',
                        'Wechatpay-Serial' => $this->platformCertificateSerial,
                        'Content-Type'     => 'application/json',
                    ],
                ]);
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data     = json_decode($response->getBody()->getContents());
                throw new \Exception($data->message);
            }
            throw new \Exception($e->getMessage());
        }

        return json_decode($res->getBody()->getContents(), true);
    }

    public function selectModifySettlementInfo(\app\admin\model\merchant\Merchant|null $merchant = null): array
    {
        $applicationNo = $merchant->merchant_info['application_no'];

        try {
            $res = $this->instance
                ->chain('/v3/apply4sub/sub_merchants/' . $merchant->merchant_id . '/application/' . $applicationNo)
                ->get([
                    'query' => [
                        'account_number_rule' => 'ACCOUNT_NUMBER_RULE_MASK_V2',
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data     = json_decode($response->getBody()->getContents());
                throw new \Exception($data->message);
            }
            throw new \Exception($e->getMessage());
        }

        return json_decode($res->getBody()->getContents(), true);
    }

    private function pullBankListFromApi(string|null $type = null): array
    {
        $path  = $type == 'corporate' ? '/v3/capital/capitallhh/banks/corporate-banking' : '/v3/capital/capitallhh/banks/personal-banking';
        $limit = self::BANK_FETCH_LIMIT;
        $all   = [];
        $totalCount = 0;

        do {
            $res        = $this->requestBankPage($path, $limit, $offset);
            $data       = $res['data'] ?? [];
            $all        = array_merge($all, $data);
            $totalCount = $res['total_count'] ?? 0;
            if ($totalCount == 0) {
                $totalCount = count($all);
            }
            $offset = $offset + $limit;
        } while (!empty($data) && $offset < $totalCount);

        return [
            'data'        => $all,
            'total_count' => $totalCount ?: count($all),
            'count'       => count($all),
            'offset'      => 0,
        ];
    }

    private function pullBranchListFromApi(string|null $bank_alias_code = null, string|null $city_code = null): array
    {
        $limit = self::BANK_FETCH_LIMIT;
        $all   = [];
        $totalCount = 0;

        do {
            $res        = $this->requestBankPage('/v3/capital/capitallhh/banks/' . $bank_alias_code . '/branches?city_code=' . $city_code, $limit, $offset, true);
            $data       = $res['data'] ?? [];
            $all        = array_merge($all, $data);
            $totalCount = $res['total_count'] ?? 0;
            if ($totalCount == 0) {
                $totalCount = count($all);
            }
            $offset = $offset + $limit;
        } while (!empty($data) && $offset < $totalCount);

        return [
            'data'        => $all,
            'total_count' => $totalCount ?: count($all),
            'count'       => count($all),
            'offset'      => 0,
        ];
    }

    private function requestBankPage(string|null $path = null, int|null $limit = null, int|null $offset = null, bool|null $isBranch = false): array
    {
        $url = $path . (str_contains($path, '?') ? '&limit=' . $limit . '&offset=' . $offset : '?limit=' . $limit . '&offset=' . $offset);

        try {
            $res = $this->instance->chain($url)->get();
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data     = json_decode($response->getBody()->getContents());
                if ($isBranch && ($data->message ?? '') === '查询结果为空') {
                    return [
                        'data'        => [],
                        'total'       => 0,
                        'offset'      => 0,
                        'total_count' => 0,
                    ];
                }
                throw new \Exception($data->message ?? '微信银行列表查询失败');
            }
            throw new \Exception($e->getMessage());
        }

        if ($res->getStatusCode() != 200 || $res->getStatusCode() != '200') {
            throw new \Exception('查询错误');
        }

        return json_decode($res->getBody()->getContents(), true);
    }

    private function paginateBankList(array|null $banks = null, int|null $limit = null, int|null $offset = null, string|null $keyword = null, bool|null $isBranch = false): array
    {
        $data    = $banks['data'] ?? [];
        $keyword = trim($keyword);

        if ($keyword !== '') {
            $kw = mb_strtolower($keyword);
            $data = array_values(array_filter($data, function ($item) use ($kw, $isBranch) {
                if ($isBranch) {
                    return str_contains(mb_strtolower($item['branch_name'] ?? ''), $kw);
                }
                return str_contains(mb_strtolower($item['bank_alias'] ?? ''), $kw) || str_contains(mb_strtolower($item['account_bank'] ?? ''), $kw);
            }));
        }

        $totalCount = count($data);
        $slice      = array_slice($data, $offset, $limit);

        return [
            'data'        => $slice,
            'total_count' => $totalCount,
            'count'       => count($slice),
            'offset'      => $offset,
            'links'       => $this->buildPaginationLinks($limit, $offset, $totalCount),
        ];
    }

    private function buildPaginationLinks(int|null $limit = null, int|null $offset = null, int|null $total = null): array
    {
        $links = [
            'next' => null,
            'prev' => null,
            'self' => '?offset=' . $offset . '&limit=' . $limit,
        ];

        if ($offset + $limit < $total) {
            $links['next'] = '?offset=' . ($offset + $limit) . '&limit=' . $limit;
        }

        if (0 < $offset) {
            $links['prev'] = '?offset=' . max(0, $offset - $limit) . '&limit=' . $limit;
        }

        return $links;
    }

    private function encryptor(string|null $msg = null): string
    {
        if (empty($msg)) {
            return '';
        }

        return \WeChatPay\Crypto\Rsa::encrypt($msg, $this->platformCertificateInstance);
    }

    private function mediaBuild(array|null $data = null): array
    {
        $urls = [];
        array_walk_recursive($data, function ($value) use (&$urls) {
            if (is_string($value) && !empty($value)) {
                $urls[] = $value;
            }
        });

        $attachments = \app\common\model\Attachment::whereIn('url', $urls)->column('media_id', 'url');

        foreach ([
            'contact_info.contact_id_doc_copy',
            'contact_info.contact_id_doc_copy_back',
            'subject_info.business_license_info.license_copy',
            'subject_info.certificate_info.cert_copy',
            'subject_info.certificate_letter_copy',
            'subject_info.identity_info.authorize_letter_copy',
            'subject_info.identity_info.id_card_info.id_card_copy',
        ] as $fieldPath) {
            $this->safeSetSingleValue($data, $fieldPath, function ($value) use ($attachments) {
                return $this->replaceVal($attachments, $value);
            });
        }

        foreach ([
            'business_info.sales_info.store_entrance_pic',
            'business_info.sales_info.biz_store_info.indoor_pic',
            'business_info.sales_info.mp_info.mp_pics',
            'business_info.sales_info.mini_program_info.mini_program_pics',
            'business_info.sales_info.app_info.app_pics',
        ] as $fieldPath) {
            $this->safeSetArrayValue($data, $fieldPath, function ($value) use ($attachments) {
                return $this->replaceArray($attachments, $value);
            });
        }

        if (isset($data['subject_info']['ubo_info_list']) && is_array($data['subject_info']['ubo_info_list'])) {
            foreach ($data['subject_info']['ubo_info_list'] as $key => &$uboItem) {
                foreach (['ubo_id_doc_copy'] as $field) {
                    if (empty($uboItem[$field])) {
                        continue;
                    }
                    $uboItem[$field] = $this->replaceVal($attachments, $uboItem[$field]);
                }
            }
        }

        return $data;
    }

    private function safeSetSingleValue(array|null &$data = null, string|null $fieldPath = null, callable|null $processor = null): void
    {
        $keys = explode('.', $fieldPath);
        $ref  = &$data;
        for ($i = 0; $i < count($keys) - 1; $i++) {
            if (!isset($ref[$keys[$i]]) || !is_array($ref[$keys[$i]])) {
                return;
            }
            $ref = &$ref[$keys[$i]];
        }
        $lastKey = $keys[count($keys) - 1];
        if (array_key_exists($lastKey, $ref)) {
            $ref[$lastKey] = $processor($ref[$lastKey]);
        }
    }

    private function safeSetArrayValue(array|null &$data = null, string|null $fieldPath = null, callable|null $processor = null): void
    {
        $keys = explode('.', $fieldPath);
        $ref  = &$data;
        for ($i = 0; $i < count($keys); $i++) {
            if (!isset($ref[$keys[$i]])) {
                return;
            }
            $ref = &$ref[$keys[$i]];
        }
        if (is_array($ref) && !empty($ref)) {
            $ref = $processor($ref);
        }
    }

    private function replaceArray(array|null $arrayContent = null, array|null $array = []): array
    {
        if (empty($array)) {
            return [];
        }
        if (empty($arrayContent)) {
            return $arrayContent;
        }

        $result = [];
        foreach ($array as $key => $url) {
            if (isset($arrayContent[$url])) {
                $decoded      = json_decode($arrayContent[$url], true);
                $result[$key] = $decoded['wxpay'] ?? $url;
            } else {
                $result[$key] = $url;
            }
        }

        return $result;
    }

    private function replaceVal(array|null $arrayContent = null, null|string $value = null): null|string
    {
        if (empty($arrayContent)) {
            return null;
        }
        if (empty($arrayContent[$value])) {
            return $value;
        }

        $decoded = json_decode($arrayContent[$value], true);

        return $decoded['wxpay'] ?? $value;
    }

    public function getConfig(): array
    {
        return [];
    }

}

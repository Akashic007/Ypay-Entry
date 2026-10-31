<?php

// 还原自 Swoole Compiler 加密文件: Alipay.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/entryChannel/Alipay.php
// [B47 opcode 全量对照还原] 字面量三密钥（fnA=1997*n fnB=2021*n fnC=1024*n）全解

namespace app\common\library\entryChannel;

class Alipay implements \app\common\service\entry\contract\AlipayOfficial
{
    protected \app\admin\model\EntryChannel $channel;
    protected array $config = [];
    protected string $appId;
    protected string $appPrivateKey;
    protected array $productTypes = ['FACE', null, 'APP', null, 'JSAPI', null, 'WAP'];
    protected array $productCodes = ['JSAPI' => 'I1080300001000060370', 'PRE_AUTH' => 'I1080300001000065324', 'WAP' => 'QUICK_WAP_WAY', 'PC' => 'FAST_INSTANT_TRADE_PAY', 'ORDER_CODE' => 'I1080300001000068149'];
    protected array $productNames = ['JSAPI' => 'JSAPI支付', 'PRE_AUTH' => '预授权支付', 'WAP' => '手机网站支付', 'PC' => '电脑网站支付', 'ORDER_CODE' => '订单码支付', 'FACE' => '当面付', 'APP' => 'APP支付'];

    public function __construct(\app\admin\model\EntryChannel $entryChannel)
    {
        $config = $entryChannel->config;
        if (empty($config)) {
            throw new \ba\Exception('通道配置不存在');
        }
        if ($config['mode'] != 'cert') {
            $config['mode'] = 'key';
        }
        $this->config = $config;
        $this->channel = $entryChannel;
        if (empty($config['appId'])) {
            throw new \ba\Exception('应用ID为空');
        }
        $this->appId = $config['appId'];
        $this->appPrivateKey = $config['appPrivateKey'];
        if (isset($config['mode']) && $config['mode'] == 'key') {
            if (empty($config['alipayPublicKey'])) {
                throw new \ba\Exception('支付宝公钥为空');
            }
        } else {
            if (empty($config['appCertPem']) || empty($config['alipayPublicCertPem']) || empty($config['alipayRootCert'])) {
                throw new \ba\Exception('缺乏必要证书文件');
            }
        }
        \Alipay\EasySDK\Kernel\Factory::setOptions($this->getOptions());
    }

    public function apply(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): void
    {
        $applyData = $mchEntry->apply_data;
        $batchNo = $mchEntry->apply_id ?? null;
        if ($mchEntry->status == 0 && empty($batchNo)) {
            $res = $this->createTransaction($applyData);
            $batchNo = $res['batch_no'];
            $mchEntry->apply_id = $batchNo;
            $mchEntry->save();
        }
        try {
            $applyData['batch_no'] = $batchNo;
            if ($applyData['product_code'] == 'FACE') {
                $this->facePaySign($applyData);
            } elseif ($applyData['product_code'] == 'APP') {
                $this->appPaySign($applyData);
            } else {
                $this->commonPaySign($applyData);
            }
        } catch (\ba\Exception $e) {
            $mchEntry->status = 3;
            $mchEntry->audit_result = $e->getMessage();
            $data = $e->getData();
            $mchEntry->save();
            throw new \ba\Exception($e->getMessage());
        } catch (\Exception $e) {
            $mchEntry->status = 3;
            $mchEntry->audit_result = $e->getMessage();
            $mchEntry->save();
            $this->cancelTransaction($batchNo);
            throw new \ba\Exception($e->getMessage());
        }
        try {
            if ($applyData['product_code'] == 'FACE' || $applyData['product_code'] == 'APP') {
                $res = $this->commitTransaction($batchNo);
            } else {
                $res = $this->commitTransactionCommon($batchNo);
            }
        } catch (\ba\Exception $e) {
            $mchEntry->audit_result = $e->getMessage();
            $mchEntry->status = 3;
            $data = $e->getData();
            if (!empty($data['sub_code']) && $data['sub_code'] == 'BATCH_STATUS_IS_FINAL') {
                $mchEntry->status = 7;
            }
            $mchEntry->save();
            throw new \ba\Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new \ba\Exception('网络异常：' . $e->getMessage());
        }
        $mchEntry->status = 1;
        $mchEntry->apply_result = $res;
        $mchEntry->save();
    }

    public function getapplyinfo(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): array
    {
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.open.agent.order.query', [], ['batch_no' => $mchEntry->apply_id]);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            $mchEntry->audit_result = $result->subMsg;
            $mchEntry->status = 0;
            $mchEntry->save();
            throw new \ba\Exception('申请单查询失败：' . $result->subMsg);
        }
        $responseArray = json_decode($result->httpBody, true)['alipay_open_agent_order_query_response'];
        file_put_contents('alipayEntry.txt', json_encode($responseArray));
        switch ($responseArray['order_status']) {
            case 'MERCHANT_INFO_HOLD':
                $mchEntry->status = 1;
                break;
            case 'MERCHANT_AUDITING':
                $mchEntry->status = 2;
                $mchEntry->audit_result = '';
                break;
            case 'MERCHANT_CONFIRM':
                $mchEntry->status = 5;
                $mchEntry->audit_result = '';
                break;
            case 'MERCHANT_CONFIRM_SUCCESS':
                $mchEntry->status = 8;
                $mchEntry->audit_result = '';
                $merchant = \app\admin\model\merchant\Merchant::where([
                    'user_id'     => $mchEntry->user_id,
                    'merchant_id' => $responseArray['merchant_pid'],
                    'ec_id'       => $mchEntry->ec_id,
                ])->find();
                if (empty($merchant)) {
                    $data = [];
                    if (!empty($responseArray['agent_app_id'])) {
                        $data['agent_app_id'] = $responseArray['agent_app_id'];
                    }
                    if (!empty($mchEntry->apply_data['mini_app_id'])) {
                        $data['mini_app_id'] = $mchEntry->apply_data['mini_app_id'];
                    }
                    if (!empty($mchEntry->apply_data['mini_app_name'])) {
                        $data['mini_app_name'] = $mchEntry->apply_data['mini_app_name'];
                    }
                    if ($mchEntry->up_id) {
                        $data['up_id'] = $mchEntry->up_id;
                    }
                    if ($mchEntry->down_id) {
                        $data['down_id'] = $mchEntry->down_id;
                    }
                    if ($mchEntry->apply_id) {
                        $data['apply_id'] = $mchEntry->apply_id;
                    }
                    $config = ['api_types' => []];
                    $productCode = $mchEntry->apply_data['product_code'] ?? null;
                    if ($productCode) {
                        $list = is_array($productCode) ? $productCode : [$productCode];
                        $config['api_types'] = $list;
                    }
                    $merchant = \app\admin\model\merchant\Merchant::create([
                        'user_id'       => $mchEntry->user_id,
                        'sp_id'         => $mchEntry->sp_id,
                        'ec_id'         => $mchEntry->ec_id,
                        'name'          => $mchEntry->apply_data['contact_name'] ?? '',
                        'platform_code' => 'alipay',
                        'merchant_id'   => $responseArray['merchant_pid'],
                        'merchant_info' => $data,
                        'config'        => $config,
                        'status'        => 1,
                        'source'        => $mchEntry->source,
                    ]);
                }
                $merchant->status = 1;
                $merchantInfo = $merchant->merchant_info ?? [];
                if (!empty($responseArray['agent_app_id'])) {
                    $merchantInfo['agent_app_id'] = $responseArray['agent_app_id'];
                }
                if (!empty($mchEntry->apply_data['mini_app_id'])) {
                    $merchantInfo['mini_app_id'] = $mchEntry->apply_data['mini_app_id'];
                }
                if (!empty($mchEntry->apply_data['mini_app_name'])) {
                    $merchantInfo['mini_app_name'] = $mchEntry->apply_data['mini_app_name'];
                }
                $merchantConfig = $merchant->config ?? [];
                if (isset($merchantConfig['api_types']) && !is_array($merchantConfig['api_types'])) {
                    $merchantConfig['api_types'] = [];
                }
                if (isset($merchantConfig['api_type']) && $merchantConfig['api_type']) {
                    $merchantConfig['api_types'] = is_array($merchantConfig['api_type']) ? $merchantConfig['api_type'] : [$merchantConfig['api_type']];
                    unset($merchantConfig['api_type']);
                }
                $productCode = $mchEntry->apply_data['product_code'] ?? null;
                if ($productCode) {
                    $list = is_array($productCode) ? $productCode : [$productCode];
                    $merchantConfig['api_types'] = array_values(array_unique(array_merge($merchantConfig['api_types'], $list)));
                }
                $merchant->merchant_info = $merchantInfo;
                $merchant->config = $merchantConfig;
                $merchant->save();
                \app\admin\model\merchant\EntryMerchant::bindMerchant($mchEntry->id, $merchant->id);
                $merchantInfo = $merchant->merchant_info ?? [];
                if (empty($merchantInfo['app_auth_token'])) {
                    $merchantInfo['get_auth_token_url'] = $this->getAppAuthTokenUrl($mchEntry);
                    $merchant->merchant_info = $merchantInfo;
                    $merchant->save();
                    $mchEntry->status = 6;
                }
                break;
            case 'MERCHANT_CONFIRM_TIME_OUT':
                $mchEntry->status = 3;
                $mchEntry->audit_result = '商户超时未确认，请重新提交申请信息';
                break;
            default:
                $mchEntry->status = 7;
                $mchEntry->audit_result = $responseArray['reject_reason'] ?? '审核失败或商户拒绝';
                \app\admin\model\merchant\EntryMerchant::unbindEntry($mchEntry->id);
                break;
        }
        $mchEntry->save();
        if (!empty($responseArray['restrict_infos']) && is_array($responseArray['restrict_infos']) && count($responseArray['restrict_infos']) > 0) {
            $msg = '';
            foreach ($responseArray['restrict_infos'] as $info) {
                $msg .= '1:';
                $reason = $info['restrict_reason'] ?? '审核不通过';
                $msg .= $this->productNames[$info['restrict_product']] . '-' . $reason;
            }
            $mchEntry->audit_result = $msg;
        }
        $applyResult = $mchEntry->apply_result ?? [];
        $applyResult['order_no'] = $responseArray['order_no'];
        $applyResult['merchant_pid'] = $responseArray['merchant_pid'];
        if (!empty($responseArray['confirm_url'])) {
            $applyResult['sign_url'] = $responseArray['confirm_url'];
            $applyResult['sign_qrcode_url'] = full_url('/qrcode?data=' . $responseArray['confirm_url']);
        }
        if (!empty($responseArray['agent_app_id'])) {
            $applyResult['agent_app_id'] = $responseArray['agent_app_id'];
        }
        $mchEntry->apply_result = $applyResult;
        $mchEntry->save();
        return [
            'status'       => $mchEntry->status,
            'status_text'  => $mchEntry->statusText,
            'business_id'  => $mchEntry->business_id,
            'audit_result' => $mchEntry->audit_result,
            'apply_result' => $mchEntry->apply_result,
        ];
    }

    public function getappauthtokenurl(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): array
    {
        $params = [
            'redirectUri' => request()->domain() . '/notify/pay/alipaySign/mchAuthCode',
            'appTypes'    => ['MOBILEAPP', 'WEBAPP'],
            'isvAppId'    => $this->config['appId'],
            'meId'        => $mchEntry->id,
        ];
        $url = \app\common\library\alipay\AppAuthToken::generateWebAuthUrl($params);
        return [
            'web_url'    => $url,
            'qrcode_url' => request()->domain() . '/qrcode?data=' . \app\common\library\alipay\AppAuthToken::generateMiniProgramAuthUrl($params),
        ];
    }

    public function handletoken(\app\admin\model\merchant\MerchantEntry|null $me = null, string|null $type = null, null|string $code = null, null|string $refresh_token = null): void
    {
        if ($type != 'authorization_code' && $type != 'app_auth_token') {
            throw new \ba\Exception('授权方式错误');
        }
        $params = [
            'grant_type' => $type,
        ];
        if ($type == 'authorization_code') {
            if (empty($code)) {
                throw new \ba\Exception('换取令牌时授权码不能为空');
            }
            $params['code'] = $code;
        }
        if ($type == 'app_auth_token') {
            if (empty($refresh_token)) {
                throw new \ba\Exception('刷新令牌为空');
            }
            $params['refresh_token'] = $refresh_token;
        }
        try {
            $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.open.auth.token.app', [], $params);
        } catch (\Exception $e) {
            throw new \ba\Exception('请求异常：' . $e->getMessage());
        }
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('令牌请求失败：' . $result->subMsg, 0, $result->toMap());
        }
        $responseArray = json_decode($result->httpBody, true)['alipay_open_auth_token_app_response'];
        $merchantInfo = array_merge($me->merchant->merchant_info, $responseArray);
        $me->status = 8;
        $me->save();
        $me->merchant->merchant_info = $merchantInfo;
        $me->merchant->save();
    }

    public function getconfig(): array
    {
        $config = [];
        if (isset($this->channel->config['payCapabilities'])) {
            $config['payCapabilities'] = $this->channel->config['payCapabilities'];
        }
        return $config;
    }

    private function createtransaction(array|null $mchData = null): array
    {
        $bizContent = [
            'account'      => $mchData['account'],
            'contact_info' => [
                'contact_name'   => $mchData['contact_name'],
                'contact_mobile' => $mchData['contact_mobile'],
            ],
        ];
        if (!empty($mchData['contact_email'])) {
            $bizContent['contact_info']['contact_email'] = $mchData['contact_email'];
        }
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.open.agent.create', [], $bizContent);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('提交事务创建失败，' . $result->subMsg);
        }
        $responseArray = json_decode($result->httpBody, true)['alipay_open_agent_create_response'];
        if (empty($responseArray['batch_no'])) {
            throw new \ba\Exception('申请编号为空');
        }
        return $responseArray;
    }

    public function committransaction(string|null $batch_no = null): array
    {
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.open.agent.confirm', [], ['batch_no' => $batch_no]);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('签约提交失败：' . $result->subMsg);
        }
        return json_decode($result->httpBody, true)['alipay_open_agent_confirm_response'];
    }

    private function committransactioncommon(string|null $batch_no = null): array
    {
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.open.agent.commonsign.confirm', [], ['batch_no' => $batch_no]);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('签约提交失败：' . $result->subMsg);
        }
        return json_decode($result->httpBody, true)['alipay_open_agent_commonsign_confirm_response'];
    }

    private function canceltransaction(string|null $batch_no = null): void
    {
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.open.agent.cancel', [], ['batch_no' => $batch_no]);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('事务取消失败，' . $result->subMsg);
        }
    }

    private function facepaysign(array|null $data = null): void
    {
        $params = $this->buildParams($data);
        if (empty($params['biz_params'])) {
            throw new \ba\Exception('参数错误');
        }
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->fileExecute('alipay.open.agent.facetoface.sign', $params['biz_params'], [], $params['image_params'] ?? []);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('当面付签约失败：' . $result->subMsg, 0, $result->toMap());
        }
    }

    private function apppaysign(array|null $data = null): void
    {
        $params = $this->buildParams($data);
        if (empty($params['biz_params'])) {
            throw new \ba\Exception('参数错误');
        }
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->fileExecute('alipay.open.agent.mobilepay.sign', $params['biz_params'], [], $params['image_params']);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('APP签约失败：' . $result->subMsg);
        }
    }

    private function commonpaysign(array|null $data = null): void
    {
        $params = $this->buildParams($data);
        if (empty($params['biz_params'])) {
            throw new \ba\Exception('签约参数错误');
        }
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->fileExecute('alipay.open.agent.common.sign', $params['biz_params'], [], $params['image_params'] ?? []);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('产品签约失败：' . $result->subMsg, 0, $result->toMap());
        }
    }

    public function querysignstatus(string|null $pid = null, array|null $product_codes = null): array
    {
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.open.agent.signstatus.query', [], [
            'product_codes' => $product_codes,
            'pid'           => $pid,
        ]);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception('查询失败：' . $result->subMsg, 0, $result->toMap());
        }
        return json_decode($result->httpBody, true)['alipay_open_agent_signstatus_query_response'];
    }

    private function buildparams(array|null $data = null): array
    {
        $bizContent = [
            'batch_no'      => $data['batch_no'],
            'mcc_code'      => $data['mcc_code'][0] . '_' . $data['mcc_code'][1],
            'rate'          => $data['rate'] ?? '0.60',
            'sign_and_auth' => true,
        ];
        if (!empty($data['business_license_no'])) {
            $bizContent['business_license_no'] = $data['business_license_no'];
        }
        $rootPath = root_path('runtime') . 'upload';
        $imageParams = [];
        if (isset($data['is_special_license_pic']) && $data['is_special_license_pic'] == 'yes') {
            $imageParams['special_license_pic'] = $rootPath . $data['special_license_pic'];
        }
        if ($data['business_license_pic']) {
            $imageParams['business_license_pic'] = $rootPath . $data['business_license_pic'];
        }
        switch ($data['product_code']) {
            case 'FACE':
                $bizContent += [
                    'shop_name'    => $data['shop_name'] ?? '',
                    'shop_address' => json_encode([
                        'country_code'   => '156',
                        'province_code'  => $data['shop_address_code'][0],
                        'city_code'      => $data['shop_address_code'][1],
                        'district_code'  => $data['shop_address_code'][2],
                        'detail_address' => $data['shop_detail_address'] ?? '',
                    ]),
                ];
                $imageParams += [
                    'shop_scene_pic'      => $rootPath . $data['shop_scene_pic'],
                    'shop_sign_board_pic' => $rootPath . $data['shop_sign_board_pic'],
                ];
                break;
            case 'APP':
                $bizContent += [
                    'app_name'     => $data['app_name'],
                    'app_status'   => $data['app_status'],
                    'app_market'   => json_encode($data['app_market']),
                    'trade_scene'  => 'mobile',
                    'mobile_type'  => 'APP',
                ];
                if ($data['app_status'] == 'OFFLINE') {
                    $imageParams += [
                        'app_home_screenshot' => $rootPath . $data['app_home_screenshot'],
                        'app_pay_screenshot'  => $rootPath . $data['app_pay_screenshot'],
                        'app_item_screenshot' => $rootPath . $data['app_item_screenshot'],
                    ];
                }
                break;
            case 'JSAPI':
                if (isset($data['mini_app_id_source']) && $data['mini_app_id_source'] == 'custom') {
                    $miniAppId = $data['mini_app_id'];
                } else {
                    if (empty($this->config['mini_app_id'])) {
                        throw new \ba\Exception('小程序APPID不能为空');
                    }
                    $miniAppId = $this->config['mini_app_id'];
                }
                $bizContent += [
                    'product_code'  => $this->productCodes['JSAPI'],
                    'mini_app_id'   => $miniAppId,
                    'trade_scene'   => 'tiny_app',
                    'mobile_type'   => 'APP',
                    'mini_app_name' => $data['mini_app_name'] ?? '',
                ];
                break;
            case 'WAP':
                $bizContent += [
                    'product_code' => $this->productCodes['WAP'],
                    'h_5_sites'    => $data['h_5_sites'],
                    'h_5_status'   => $data['h_5_status'],
                    'trade_scene'  => 'mobile',
                    'mobile_type'  => 'H5',
                ];
                if ($data['h_5_status'] == 'OFFLINE') {
                    $imageParams += [
                        'h_5_home_screenshot' => $rootPath . $data['h_5_home_screenshot'],
                        'h_5_item_screenshot' => $rootPath . $data['h_5_item_screenshot'],
                        'h_5_pay_screenshot'  => $rootPath . $data['h_5_pay_screenshot'],
                    ];
                }
                if (!empty($data['h_5_sites_loa'])) {
                    $imageParams += ['h_5_sites_loa' => $rootPath . $data['h_5_sites_loa']];
                }
                break;
            case 'PC':
                $bizContent = [
                    'product_code' => $this->productCodes['PC'],
                    'web_sites'    => [$data['web_sites']],
                    'web_status'   => $data['web_status'],
                    'trade_scene'  => 'pc',
                ];
                if (!empty($data['web_sites_loa'])) {
                    $imageParams += ['web_sites_loa' => $rootPath . $data['web_sites_loa']];
                }
                if ($data['web_status'] == 'OFFLINE') {
                    $imageParams += [
                        'web_home_screenshot' => $rootPath . $data['web_home_screenshot'],
                        'web_item_screenshot' => $rootPath . $data['web_item_screenshot'],
                        'web_pay_screenshot'  => $rootPath . $data['web_pay_screenshot'],
                    ];
                }
                break;
            case 'PRE_AUTH':
            case 'ORDER_CODE':
            default:
                throw new \ba\Exception('当前不支持开通该支付产品');
        }
        return [
            'biz_params'   => $bizContent,
            'image_params' => $imageParams,
        ];
    }

    public function uploadmedia(\app\common\model\Attachment|null $attachment = null): array
    {
        return [];
    }

    private function getoptions(): \Alipay\EasySDK\Kernel\Config
    {
        $config = new \Alipay\EasySDK\Kernel\Config();
        $config->protocol = 'https';
        $config->gatewayHost = 'openapi.alipay.com';
        $config->signType = 'RSA2';
        $config->appId = $this->appId;
        $config->merchantPrivateKey = $this->appPrivateKey;
        if ($this->config['mode'] == 'key') {
            $config->alipayPublicKey = $this->config['alipayPublicKey'];
        } else {
            $certPath = root_path('runtime') . DIRECTORY_SEPARATOR . 'upload';
            $config->alipayCertPath = $certPath . $this->config['alipayPublicCertPem'];
            $config->alipayRootCertPath = $certPath . $this->config['alipayRootCert'];
            $config->merchantCertPath = $certPath . $this->config['appCertPem'];
        }
        if (!empty($this->config['encryptKey'])) {
            $config->encryptKey = $this->config['encryptKey'];
        }
        return $config;
    }
}

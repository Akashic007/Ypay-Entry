<?php

// 还原自 Swoole Compiler 加密文件: Wxpay.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/entryChannel/up/Wxpay.php

namespace app\common\library\entryChannel\up;

class Wxpay extends \app\common\library\entryChannel\up\UpEntry implements \app\common\service\entry\contract\WxpayOfficial {
    public function __construct(\app\admin\model\EntryChannel $channel)
    {
    parent::__construct($channel);
    }

    public function apply(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null, bool|null $draft = null): void
    {
    $applyData = $this->mediaBuild($mchEntry->apply_data);
    $mchEntry->apply_data = $applyData;
    parent::apply($mchEntry, $draft);
    }

    public function querybanklist(string|null $type = null, int|null $page = null, int|null $limit = null, string|null $keyword = null): array
    {
    $request = new \app\common\library\upstream\UpRequest($this->upstream);
    $request->setUrl('/supply/entry.Wxpay/getBankList');
    $request->setParams([
        'type'    => $type,
        'page'    => $page,
        'limit'   => $limit,
        'keyword' => $keyword,
        'ec_id'   => $this->upChannelId,
    ]);
    try {
        $response = $request->httpUrl();
    } catch (Exception $e) {
        throw new \ba\Exception($e->getMessage());
    }
    if ($response['code'] != 1) {
        throw new \ba\Exception($response['msg']);
    }
    return $response['data'];
    }

    public function querybankbranchlist(string|null $bank_alias_code = null, string|null $city_code = null, int|null $page = null, int|null $limit = null, string|null $keyword = null): array
    {
    $request = new \app\common\library\upstream\UpRequest($this->upstream);
    $request->setUrl('/supply/entry.Wxpay/getBankBranchList');
    $request->setParams([
        'bank_alias_code' => $bank_alias_code,
        'city_code'       => $city_code,
        'keyword'         => $keyword,
        'page'            => $page,
        'limit'           => $limit,
        'ec_id'           => $this->upChannelId,
    ]);
    try {
        $response = $request->httpUrl();
    } catch (Exception $e) {
        throw new \ba\Exception($e->getMessage());
    }
    if ($response['code'] != 1) {
        throw new \ba\Exception($response['msg']);
    }
    return $response['data'];
    }

    public function queryprovincelist(): array
    {
    $request = new \app\common\library\upstream\UpRequest($this->upstream);
    $request->setUrl('/supply/entry.Wxpay/getProvinceList');
    $request->setParams([
        'ec_id' => $this->upChannelId,
    ]);
    try {
        $response = $request->httpUrl('GET');
    } catch (Exception $e) {
        throw new \ba\Exception($e->getMessage());
    }
    if ($response['code'] != 1) {
        throw new \ba\Exception($response['msg']);
    }
    return $response['data'];
    }

    public function querycitylist(string|null $province_code = null): array
    {
    $request = new \app\common\library\upstream\UpRequest($this->upstream);
    $request->setUrl('/supply/entry.Wxpay/getCityList');
    $request->setParams([
        'province_code' => $province_code,
        'ec_id'         => $this->upChannelId,
    ]);
    try {
        $response = $request->httpUrl('GET');
    } catch (Exception $e) {
        throw new \ba\Exception($e->getMessage());
    }
    if ($response['code'] != 1) {
        throw new \ba\Exception($response['msg']);
    }
    return $response['data'];
    }

    private function mediabuild(array|null $data = null): array
    {
    $attachments = $this->collectAttachments($data);
    // [opcode 对照] 路径清单按 arData 原始序直读（dump 显示层截断：组1实际 13 条、组2实际 10 条）
    foreach (['contact_info.contact_id_doc_copy', 'contact_info.contact_id_doc_copy_back', 'subject_info.business_license_info.license_copy', 'subject_info.certificate_info.cert_copy', 'subject_info.certificate_letter_copy', 'subject_info.identity_info.authorize_letter_copy', 'subject_info.identity_info.id_card_info.id_card_copy', 'subject_info.identity_info.id_card_info.id_card_national', 'subject_info.identity_info.id_doc_info.id_doc_copy', 'subject_info.identity_info.id_doc_info.id_doc_copy_back', 'business_info.sales_info.web_info.web_authorisation', 'addition_info.legal_person_commitment', 'addition_info.legal_person_video'] as $path) {
        $ref = &$this->refByPath($data, $path, false);
        if (!is_null($ref)) {
            $ref = $this->resolveUpUrl($attachments, $ref);
        }
    }
    foreach (['business_info.sales_info.store_entrance_pic', 'business_info.sales_info.biz_store_info.indoor_pic', 'business_info.sales_info.mp_info.mp_pics', 'business_info.sales_info.mini_program_info.mini_program_pics', 'business_info.sales_info.app_info.app_pics', 'business_info.sales_info.wework_info.wework_pics', 'subject_info.finance_institution_info.finance_license_pics', 'settlement_info.qualifications', 'settlement_info.activities_additions', 'addition_info.business_addition_pics'] as $path) {
        $ref = &$this->refByPath($data, $path, true);
        if (is_array($ref) && !empty($ref)) {
            $ref = array_map(function ($url) use ($attachments) {
                return $this->resolveUpUrl($attachments, $url);
            }, $ref);
        }
    }
    // [opcode 对照] FE_RESET_RW 直取 COALESCE 临时数组，PHP8.2 语义下回写不落回 $data（与原编译字节码行为一致，原样保留）
    foreach ($data['subject_info']['ubo_info_list'] ?? [] as &$uboItem) {
        foreach (['ubo_id_doc_copy', 'ubo_id_doc_copy_back'] as $field) {
            if (empty($uboItem[$field])) {
                continue;
            }
            $uboItem[$field] = $this->resolveUpUrl($attachments, $uboItem[$field]);
        }
    }
    unset($uboItem);
    return $data;
    }

    private function collectattachments(array|null $data = null): array
    {
    $urls = [];
    array_walk_recursive($data, function ($value) use (&$urls) {
        if (is_string($value) && $value !== '') {
            $urls[] = $value;
        }
    });
    return $urls ? \app\common\model\Attachment::whereIn('url', $urls)->column('extend', 'url') : [];
    }

    // [opcode 对照] 签名修正：arg1 type_mask=0x2000080(引用+array)、3 个 RECV 无默认、方法为 & 引用返回(RETURN_BY_REF)；原降级签名的 =null 默认与无 & 均为失真
    private function &refbypath(array &$data, string $path, bool $isLeaf): mixed
    {
    $node = null;
    $paths = explode('.', $path);
    $ref = &$data;
    $len = $isLeaf ? count($paths) : count($paths) - 1;
    for ($i = 0; $i < $len; ++$i) {
        if (!isset($ref[$paths[$i]]) || !is_array($ref[$paths[$i]])) {
            return $node;
        }
        $ref = &$ref[$paths[$i]];
    }
    if (!$isLeaf) {
        $last = $paths[count($paths) - 1];
        if (!array_key_exists($last, $ref)) {
            return $node;
        }
        $ref = &$ref[$last];
    }
    return $ref;
    }

    private function resolveupurl(array|null $attachments = null, null|string $url = null): null|string
    {
    if (empty($url) || !isset($attachments[$url])) {
        return $url;
    }
    $up = $attachments[$url];
    if (is_string($up)) {
        $up = json_decode($up, true);
    }
    return $up['up_url'] ?? $url;
    }

}

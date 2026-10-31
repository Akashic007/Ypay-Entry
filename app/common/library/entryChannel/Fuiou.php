<?php

// 还原自 Swoole Compiler 加密文件: Fuiou.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/entryChannel/Fuiou.php
// [B47 opcode 全量对照还原] 字面量三密钥（fnA=1997*n fnB=2021*n fnC=1024*n）全解

namespace app\common\library\entryChannel;

class Fuiou implements \app\common\service\entry\contract\FuiouPayInterface
{
    protected \app\admin\model\EntryChannel $channel;

    protected string $apiUrl = 'http://www-1.fuiou.com:28090/wmp/wxMchntMng.fuiou?action=';

    public function __construct(\app\admin\model\EntryChannel $entryChannel)
    {
        $this->channel = $entryChannel;
    }

    public function apply(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): void
    {
        $config = $this->channel->config;
        $params = array_merge($mchEntry->apply_data, [
            'trace_no' => $mchEntry->business_id,
            'ins_cd'   => $config['institutionId'],
        ]);
        if (isset($params['county_cd']) && is_array($params['county_cd'])) {
            if (empty($params['county_cd'][1])) {
                throw new \Exception('城市编码错误');
            }
            $params['city_cd'] = $params['county_cd'][1];
            if (empty($params['county_cd'][2])) {
                throw new \Exception('县编码错误');
            }
            $params['county_cd'] = $params['county_cd'][2];
        }
        if (isset($params['business']) && is_array($params['business'])) {
            if (empty($params['business'][1])) {
                throw new \Exception('微信经营范围错误');
            }
            $params['business'] = $params['business'][1];
        }
        $res = $this->buildReq('wxMchntAdd', $params);
        if ($res['ret_code'] !== '0000') {
            $mchEntry->audit_result = $res['ret_msg'];
            $mchEntry->status = 3;
            $mchEntry->save();
            throw new \Exception($res['ret_msg']);
        }
        if (!empty($res['fy_mchnt_cd'])) {
            $mchEntry->status = 4;
            $mchEntry->audit_result = '';
            $mchEntry->apply_id = $res['fy_mchnt_cd'];
            $mchEntry->save();
            return;
        }
        $mchEntry->status = 2;
        $mchEntry->save();
    }

    public function getapplyinfo(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): array
    {
        if (empty($mchEntry->apply_data['mchnt_name'])) {
            throw new \Exception('商户名称不能为空');
        }
        $config = $this->channel->config;
        $params = [
            'insCd'     => $config['institutionId'],
            'traceNo'   => $mchEntry->business_id,
            'mchntName' => $mchEntry->apply_data['mchnt_name'],
        ];
        $res = $this->buildReq('getMchntInfAndConfig', $params, 'fsnl');
        if (!empty($res['mchntCd'])) {
            $mchEntry->apply_id = $res['mchntCd'];
            $mchEntry->status = 4;
            $mchEntry->audit_result = '';
            $mchEntry->save();
        }
        return $res;
    }

    public function generateelectroncontract(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): array
    {
        $config = $this->channel->config;
        $params = [
            'ins_cd'           => $config['institutionId'],
            'mchnt_cd'         => $mchEntry->apply_id,
            'trace_no'         => $mchEntry->business_id,
            'certif_phone'     => '15975210403',
            'auth_type'        => 1,
            'reach_type'       => 2,
            'notify_url'       => request()->domain() . '/notify/entry/fuiou/signContract',
            'front_return_url' => request()->domain() . '/notify/entry/fuiou/signContract',
        ];
        $res = $this->buildReq('http://www-1.fuiou.com:28090/wmp/mchntsignv2.fuiou?action=elecContractSignV2', $params, 'fsnl');
        if (!empty($res['contract_no'])) {
            $applyResult = $mchEntry->apply_result ?? [];
            $applyResult['contract_no'] = $res['contract_no'];
            $mchEntry->apply_result = $applyResult;
            $mchEntry->status = 5;
            $mchEntry->save();
        }
        return $res;
    }

    public function getsigncontractstatus(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): array
    {
        $config = $this->channel->config;
        $params = [
            'ins_cd'       => $config['institutionId'],
            'mchnt_cd'     => $mchEntry->apply_id,
            'trace_no'     => $mchEntry->business_id,
            'contract_no'  => 'PB01165220260114030903', // [opcode 对照] 硬编码字面量真值（lit9）
        ];
        return $this->buildReq('http://www-1.fuiou.com:28090/wmp/mchntsignv2.fuiou?action=elecContractSignV2StatusQuery', $params, 'fsnl');
    }

    public function getelectroncontract(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): array
    {
        $config = $this->channel->config;
        $params = [
            'ins_cd'   => $config['institutionId'],
            'mchnt_cd' => $mchEntry->apply_id,
            'trace_no' => $mchEntry->business_id,
        ];
        return $this->buildReq('http://www-1.fuiou.com:28090/wmp/mchntsignv2.fuiou?action=elecContractSignV2Query', $params, 'fsnl');
    }

    public function mchnamecheck(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): array
    {
        $config = $this->channel->config;
        $params = [
            'trace_no'   => $mchEntry->business_id,
            'ins_cd'     => $config['institutionId'],
            'mchnt_name' => $mchEntry->apply_data['mchnt_name'],
        ];
        return $this->buildReq('wxMchntNameCheck', $params);
    }

    public function uploadmedia(\app\common\model\Attachment|null $attachment = null): array
    {
        // opcode 真值: return []
return [];
    }

    private function generatesign(array|null $params = null): string
    {
        unset($params['sign']);
        $params = array_filter($params, function ($v) {
            // [opcode 对照] JMPZ_EX+TYPE_CHECK(ext=1020 非空)：保留非空串且非 null 的值
            return $v != '' && !is_null($v);
        });
        ksort($params);
        $str = '';
        foreach ($params as $k => $v) {
            $v = mb_convert_encoding((string)$v, 'GBK', 'UTF-8');
            $str .= $k . '=' . $v . '&';
        }
        $str .= 'key=' . mb_convert_encoding($this->channel->config['entryMd5Key'], 'GBK', 'UTF-8');
        return strtolower(md5($str));
    }

    private function buildreq(string|null $url = null, array|null $params = null, string|null $pac = null): array
    {
        $params['sign'] = $this->generateSign($params);
        if ($pac == 'xml') {
            $xml = $this->generateXml($params);
            $str = mb_convert_encoding($xml, 'GBK', 'UTF-8');
            $data = urlencode($str);
        } else {
            $data = json_encode($params);
        }
        if (!is_url($url)) {
            $url = $this->apiUrl . $url;
        }
        $res = httpCurl($url, 'POST', ['req' => $data]);
        if ($res['status'] !== 200) {
            throw new \Exception('请求错误：' . ($res['error'] ?? '未知错误'));
        }
        if ($pac == 'xml') {
            return $this->parseXml(urldecode($res['body']));
        }
        // [opcode 对照] COALESCE 回退字面量为数组 {'msg':'成功'}（lit46），按真值保留
        return json_decode($res['body'] ?? ['msg' => '成功'], true);
    }

    private function generatexml(array|null $params = null): string
    {
        $xml = '<?xml version="1.0" encoding="GBK" standalone="yes"?><xml>';
        foreach ($params as $k => $v) {
            // [opcode 对照] 空/NULL 值跳过（受保护比较指令，语义为空值不参与 XML 组装）
            if ($v == '' || $v === null) {
                continue;
            }
            $xml .= '<' . $k . '>' . $v . '</' . $k . '>';
        }
        $xml .= '</xml>';
        return $xml;
    }

    private function parsexml(string|null $xml = null): array
    {
        // [opcode 对照] SEND 字面量 'SimpleXMLElement'（裸字符串，避免命名空间解析）
        $xmlObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode($xmlObj), true);
    }

    public function getconfig(): array
    {
        // opcode 真值: return null（行 295，可空返回）
return null;
    }
}

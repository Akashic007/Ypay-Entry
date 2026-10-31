<?php

// 还原自 Swoole Compiler 加密文件: UnifyOcr.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/aliyun/UnifyOcr.php

namespace app\common\library\aliyun;

class UnifyOcr{
    protected string $accessKeyId;
    protected string $accessKeySecret;
    protected \AlibabaCloud\SDK\Ocrapi\V20210707\Ocrapi $client;
    public function __construct(string $accessKeyId, string $accessKeySecret)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $config = new \Darabonba\OpenApi\Models\Config([
            'accessKeyId' => $this->accessKeyId,
            'accessKeySecret' => $this->accessKeySecret,
        ]);
        $config->endpoint = 'ocr-api.cn-hangzhou.aliyuncs.com';
        $this->client = new \AlibabaCloud\SDK\Ocrapi\V20210707\Ocrapi($config);
    }

    // [opcode 对照] $type/$options 默认值按 RECV_INIT op2=LIT('General')/LIT(<arr>{}) 真值补回（原签名 null 为管线丢失）
    public function recognize(string|null $image = null, string|null $type = 'General', array|null $options = []): array
    {
        if (!$this->valImageType($type)) {
            throw new Exception('图片类型错误');
        }
        $stream = null;
        try {
            $request = new \AlibabaCloud\SDK\Ocrapi\V20210707\Models\RecognizeAllTextRequest();
            if (is_url($image)) {
                $request->url = $image;
            } else {
                $handle = fopen($image, 'rb');
                $stream = new \GuzzleHttp\Psr7\Stream($handle);
                $request->body = $stream;
            }
            $request->type = $type;
            $res = $this->client->recognizeAllText($request);
        } catch (Throwable $e) {
            if ($e instanceof \AlibabaCloud\Tea\Exception\TeaError) {
                throw new Exception($e->message);
            }
            $e = new \AlibabaCloud\Tea\Exception\TeaError([], $e->getMessage(), $e->getCode(), $e);
            throw new Exception($e->message);
        } finally {
            if ($stream instanceof \GuzzleHttp\Psr7\Stream) {
                $stream->close();
            }
        }
        $res = $res->body->data->toMap();
        return $this->handleContent($res['SubImages'][0]['KvInfo']['Data'], $type);
    }

    // [opcode 对照] $type 默认值按 RECV_INIT op2=LIT('General') 真值补回（原签名 null 为管线丢失）
    public function handlecontent(array|null $data = null, string|null $type = 'General'): array
    {
        switch ($type) {
            case 'IdCard':
                if (isset($data['name'])) {
                    return [
                        'name' => $data['name'],
                        'id_number' => $data['idNumber'] ?? '',
                        'ethnicity' => $data['ethnicity'] ?? '',
                        'birth_date' => $data['birthDate'] ?? '',
                        'address' => $data['address'] ?? '',
                        'sex' => $data['sex'] ?? '',
                    ];
                }
                return [
                    'valid_period' => $data['validPeriod'] ?? '',
                    'issue_authority' => $data['issueAuthority'] ?? '',
                ];
            case 'BusinessLicense':
                return [
                    'registration_date' => $data['RegistrationDate'] ?? '',
                    'business_address' => $data['businessAddress'] ?? '',
                    'business_scope' => $data['businessScope'] ?? '',
                    'company_form' => $data['companyForm'] ?? '',
                    'company_name' => $data['companyName'] ?? '',
                    'company_type' => $data['companyType'] ?? '',
                    'credit_code' => $data['creditCode'] ?? '',
                    'issue_date' => $data['issueDate'] ?? '',
                    'legal_person' => $data['legalPerson'] ?? '',
                    'registered_capital' => $data['registeredCapital'] ?? '',
                    'title' => $data['title'] ?? '',
                    'valid_from_date' => $data['validFromDate'] ?? '',
                    'valid_period' => $data['validPeriod'] ?? '',
                    'valid_to_date' => $data['validToDate'] ?? '',
                ];
        }
        return $data;
    }

    private function valimagetype(string|null $type = null): bool
    {
        return in_array($type, ['General', 'Advanced', 'IdCard', 'InternationalIdCard', 'HKIdCard', 'BusinessLicense', 'InternationalBusinessLicense']);
    }

}

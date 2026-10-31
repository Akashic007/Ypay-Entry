<?php

// 还原自 Swoole Compiler 加密文件: WechatFace.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/realName/WechatFace.php

namespace app\common\library\realName;

class WechatFace{
    private array $config = [];
    private string $accessToken = '';
    private mixed $ticket;
    public function __construct()
    {
        $config = get_sys_config('real_name_config');
        if (empty($config)) {
            throw new \ba\Exception('实名认证配置不存在');
        }
        if ($config['type'] !== 'tencent_wx_face_verify') {
            throw new \ba\Exception('非微信人脸识别配置');
        }
        if (empty($config['appId'])) {
            throw new \ba\Exception('微信人脸认证配置不完整');
        }
        if (empty($config['secretKey'])) {
            throw new \ba\Exception('微信人脸认证配置不完整');
        }
        $this->config = [
            'appId'     => $config['appId'],
            'secretKey' => $config['secretKey'],
        ];
        $accessToken = $this->getAccessToken();
        $this->accessToken = $accessToken;
        $ticket = $this->getTicket();
        $this->ticket = $ticket;
    }


    public function geturl(string|null $name = null, string|null $idNo = null, int|null $userId = null): string
    {
        $nonce = generate_random_string();
        $data = [
            'version' => '1.0.0',
            'userId'  => $userId,
            'ticket'  => $this->ticket,
            'nonce'   => $nonce,
            'appId'   => $this->config['appId'],
        ];
        $sign = $this->getSign($data);
        $orderNo = \app\common\service\OrderService::generateOrderNo();
        $params = [
            'appId'   => $this->config['appId'],
            'orderNo' => $orderNo,
            'name'    => $name,
            'idNo'    => $idNo,
            'userId'  => $userId,
            'version' => '1.0.0',
            'nonce'   => $nonce,
            'sign'    => $sign,
        ];
        $url = 'https://kyc1.qcloud.com/api/server/getAdvFaceId?orderNo=' . $orderNo;
        $res = httpCurl($url, 'POST', $params, [], true, '', '', '', 10, '');
        $res = $res['body'];
        if (!isset($res['code']) || $res['code'] != 0) {
            throw new \ba\Exception('微信人脸获取url失败：' . $res['msg']);
        }
        $result = $res['result'];
        $result['requestData'] = $params;
        $result['nonceTicket'] = $this->getNonceTicket($userId);
        $url = $this->startRealName($result);
        $data = [
            'user_id'     => $userId,
            'name'        => $name,
            'type'        => 'personal',
            'id_no'       => $idNo,
            'business_id' => $orderNo,
            'status'      => 2,
        ];
        $realName = \app\admin\model\user\RealName::where('user_id', $userId)->find();
        if ($realName) {
            $realName->save($data);
        } else {
            \app\admin\model\user\RealName::create($data);
        }
        return $url;
    }


    public function getadminurl(string|null $name = null, string|null $idNo = null, int|null $adminId = null): string
    {
        $nonce = generate_random_string();
        $data = [
            'version' => '1.0.0',
            'userId'  => $adminId,
            'ticket'  => $this->ticket,
            'nonce'   => $nonce,
            'appId'   => $this->config['appId'],
        ];
        $sign = $this->getSign($data);
        $orderNo = \app\common\service\OrderService::generateOrderNo();
        $params = [
            'appId'   => $this->config['appId'],
            'orderNo' => $orderNo,
            'name'    => $name,
            'idNo'    => $idNo,
            'userId'  => $adminId,
            'version' => '1.0.0',
            'nonce'   => $nonce,
            'sign'    => $sign,
        ];
        $url = 'https://kyc1.qcloud.com/api/server/getAdvFaceId?orderNo=' . $orderNo;
        $res = httpCurl($url, 'POST', $params, [], true, '', '', '', 10, '');
        $res = $res['body'];
        if (!isset($res['code']) || $res['code'] != 0) {
            throw new \ba\Exception('微信人脸获取url失败：' . $res['msg']);
        }
        $result = $res['result'];
        $result['requestData'] = $params;
        $result['nonceTicket'] = $this->getNonceTicket($adminId);
        $url = $this->startRealName($result, request()->domain() . '/notify/adminRealName/wechat');
        $data = [
            'admin_id'    => $adminId,
            'name'        => $name,
            'id_no'       => $idNo,
            'business_id' => $orderNo,
            'status'      => 2,
        ];
        $realName = \app\admin\model\auth\RealName::where('admin_id', $adminId)->find();
        if ($realName) {
            $realName->save($data);
        } else {
            \app\admin\model\auth\RealName::create($data);
        }
        return $url;
    }


    public function checkrealnamestatus(string|null $orderNo = null): void
    {
        $nonce = generate_random_string();
        $data = [
            'appId'      => $this->config['appId'],
            'orderNo'    => $orderNo,
            'version'    => '1.0.0',
            'api ticket' => $this->ticket,
            'nonce'      => $nonce,
        ];
        $sign = $this->getSign($data);
        unset($data['api ticket']);
        $data['sign'] = $sign;
        $url = 'https://kyc1.qcloud.com/api/v2/base/queryfacerecord?orderNo=' . $orderNo;
        $res = httpCurl($url, 'POST', $data, [], true, '', '', '', 10, '');
        $res = $res['body'];
        if (!isset($res['code']) || $res['code'] != 0) {
            throw new \ba\Exception('微信人脸核身失败：' . $res['msg']);
        }
        \app\admin\model\user\RealName::where('business_id', $orderNo)->update([
            'status'    => 1,
            'real_time' => time(),
        ]);
    }


    public function checkadminrealnamestatus(string|null $orderNo = null): void
    {
        $nonce = generate_random_string();
        $data = [
            'appId'      => $this->config['appId'],
            'orderNo'    => $orderNo,
            'version'    => '1.0.0',
            'api ticket' => $this->ticket,
            'nonce'      => $nonce,
        ];
        $sign = $this->getSign($data);
        unset($data['api ticket']);
        $data['sign'] = $sign;
        $url = 'https://kyc1.qcloud.com/api/v2/base/queryfacerecord?orderNo=' . $orderNo;
        $res = httpCurl($url, 'POST', $data, [], true, '', '', '', 10, '');
        $res = $res['body'];
        if (!isset($res['code']) || $res['code'] != 0) {
            throw new \ba\Exception('微信人脸核身失败：' . $res['msg']);
        }
        \app\admin\model\auth\RealName::where('business_id', $orderNo)->update([
            'status'    => 1,
            'real_time' => time(),
        ]);
    }


    private function startrealname($data, string|null $callbackUrl = ''): string
    {
        $callbackUrl = $callbackUrl ?: request()->domain() . '/notify/realName/wechat';
        $nonce = generate_random_string();
        $query = [
            'appId'   => $data['requestData']['appId'],
            'userId'  => $data['requestData']['userId'],
            'nonce'   => $nonce,
            'version' => '1.0.0',
            'faceId'  => $data['faceId'],
            'orderNo' => $data['orderNo'],
            'ticket'  => $data['nonceTicket'],
        ];
        $sign = $this->getSign($query);
        $params = [
            'appId'        => $data['requestData']['appId'],
            'userId'       => $data['requestData']['userId'],
            'nonce'        => $nonce,
            'version'      => '1.0.0',
            'faceId'       => $data['faceId'],
            'orderNo'      => $data['orderNo'],
            'resultType'   => 1,
            'url'          => $callbackUrl,
            'sign'         => $sign,
            'from'         => 'browser',
            'redirectType' => '1',
        ];
        $queryString = http_build_query($params);
        return 'https://' . $data['optimalDomain'] . '/api/web/login?' . $queryString;
    }


    private function getnonceticket($userId)
    {
        $url = 'https://kyc1.qcloud.com/api/oauth2/api_ticket?appId=' . $this->config['appId'] . '&access_token=' . $this->accessToken . '&type=NONCE&user_id=' . $userId . '&version=1.0.0';
        $res = httpCurl($url);
        $res = json_decode($res['body'], true);
        if (!isset($res['code']) || $res['code'] != 0) {
            throw new \ba\Exception('微信人脸核身随机ticket获取失败');
        }
        return $res['tickets'][0]['value'];
    }


    private function getsign($data): string
    {
        sort($data, SORT_STRING);
        $str = implode('', $data);
        return sha1($str);
    }


    private function getaccesstoken()
    {
        $accessToken = \think\facade\Cache::get('WechatRealNameAccessToken', '');
        if (empty($accessToken)) {
            $url = 'https://kyc1.qcloud.com/api/oauth2/access_token?appId=' . $this->config['appId'] . '&secret=' . $this->config['secretKey'] . '&grant_type=client_credential&version=1.0.0';
            $res = httpCurl($url);
            $res = json_decode($res['body'], true);
            if (!isset($res['code']) || $res['code'] != 0) {
                throw new \ba\Exception('微信人脸核身获取access_token失败');
            }
            \think\facade\Cache::set('WechatRealNameAccessToken', $res['access_token'], 1200);
            $accessToken = $res['access_token'];
        }
        return $accessToken;
    }


    private function getticket()
    {
        $ticket = \think\facade\Cache::get('WechatRealNameTicket', '');
        if (empty($ticket)) {
            $url = 'https://kyc1.qcloud.com/api/oauth2/api_ticket?appId=' . $this->config['appId'] . '&access_token=' . $this->accessToken . '&type=SIGN&version=1.0.0';
            $res = httpCurl($url);
            $res = json_decode($res['body'], true);
            if (!isset($res['code']) || $res['code'] != 0) {
                throw new \ba\Exception('微信人脸核身获取ticket失败');
            }
            \think\facade\Cache::set('WechatRealNameTicket', $res['tickets'][0]['value'], 1200);
            $ticket = $res['tickets'][0]['value'];
        }
        return $ticket;
    }


}

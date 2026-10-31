<?php

// 还原自 Swoole Compiler 加密文件: SupplyService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/SupplyService.php

namespace app\common\service;

class SupplyService{
    public function notifydowndocking(\app\admin\model\supply\Downstream|null $down = null): array
    {
        // [opcode 对照] lit: 'status'/'api_id'/'rejected'/'reject_msg'/'agent'/'api_key', SignUtil::generateSign, httpCurl('POST'), '请求异常:' ?? '未知错误'
        $params = [
            'status' => $down->status,
            'api_id' => $down->agent->api_id,
        ];
        if (!empty($down->rejected)) {
            $params['reject_msg'] = $down->rejected;
        }
        $params['sign'] = \app\common\library\SignUtil::generateSign($params, $down->agent->api_key);
        $url = request()->domain() . '/notify/supply/upstream/dockingCallback';
        $res = httpCurl($url, 'POST', $params);
        if ($res['status'] != 200) {
            throw new \ba\Exception('请求异常:' . ($res['error'] ?? '未知错误'));
        }
        return json_decode($res['body'], true);
    }

    public function applyupstreamdocking(\app\admin\model\supply\Upstream|null $up = null): array
    {
        // [opcode 对照] lit: 'api_id'/'domain'/request()->domain(), SignUtil::generateSign, $up->api, httpCurl('POST'), '请求失败'
        $params = [
            'api_id' => $up->api_id,
            'domain' => request()->domain(),
        ];
        $params['sign'] = \app\common\library\SignUtil::generateSign($params, $up->api_key);
        $url = $up->api . '/supply/Docking/applyDocking';
        $res = httpCurl($url, 'POST', $params);
        if ($res['status'] != 200) {
            throw new \ba\Exception($res['error'] ?? '请求失败');
        }
        return json_decode($res['body'], true);
    }

    public function queryupstreamdockingstatus(\app\admin\model\supply\Upstream|null $up = null): array
    {
        // [opcode 对照] lit: 'api_id'/'domain', SignUtil::generateSign, $up->api, httpCurl('GET'), '请求异常:' ?? $res['status']
        $params = [
            'api_id' => $up->api_id,
            'domain' => request()->domain(),
        ];
        $params['sign'] = \app\common\library\SignUtil::generateSign($params, $up->api_key);
        $url = $up->api . '/supply/Docking/getDockingInfo';
        $res = httpCurl($url, 'GET', $params);
        if ($res['status'] != 200) {
            throw new \ba\Exception('请求异常:' . ($res['error'] ?? $res['status']));
        }
        return json_decode($res['body'], true);
    }

}

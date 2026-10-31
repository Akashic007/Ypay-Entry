<?php

// 还原自 Swoole Compiler 加密文件: WxpayServiceProvider.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/complaintNotify/WxpayServiceProvider.php
//
// [2026-09-13 opcode 全量对照还原] 微信服务商机投诉回调：
//   ec_id 参数 → 通道定位 → 通道配置校验 → RequestTool 初始化(catch Exception) →
//   verifysign 签名校验 → complaint_id 数据校验 → Queue push ComplaintJob@getDetail → 200
//   所有异常路径返回 FAIL + 401

namespace app\api\controller\complaintNotify;

use app\admin\model\EntryChannel;
use app\common\library\wechat\RequestTool;
use think\exception\HttpResponseException;
use think\facade\Queue;
use think\Request;
use think\Response;
use Throwable;

class WxpayServiceProvider extends \app\common\controller\Frontend
{
    protected array $noNeedLogin = ['*'];

    public function callback(): \think\Response
    {
        $ecId = request()->param('ec_id') ?? '';

        if (empty($ecId)) {
            return json(['code' => 'FAIL', 'message' => '通道ID为空'])->code(401);
        }

        $entryChannel = EntryChannel::find($ecId) ?? null;
        if (empty($entryChannel)) {
            return json(['code' => 'FAIL', 'message' => '通道不存在'])->code(401);
        }

        if (empty($entryChannel->config)) {
            return json(['code' => 'FAIL', 'message' => '通道配置不存在'])->code(401);
        }

        try {
            $requestTool = new RequestTool($entryChannel->config);
        } catch (\Exception $e) {
            return json(['code' => 'FAIL', 'message' => '请求工具初始化失败'])->code(401);
        }

        // 签名校验
        if (!$requestTool->verifysign(request()->param() ?? [])) {
            return json(['code' => 'FAIL', 'message' => '签名验证失败'])->code(401);
        }

        $data = request()->param() ?? [];

        if (empty($data) || empty($data['complaint_id'])) {
            return json(['code' => 'FAIL', 'message' => '数据为空'])->code(401);
        }

        // 投递到队列异步处理投诉详情
        Queue::push('ComplaintJob@getDetail', [
            'complaint_id' => $data['complaint_id'],
            'ec_id'        => $ecId,
            'action_type'  => $data['action_type'] ?? '',
        ]);

        return \think\Response::create()->code(200);
    }
}

<?php

// 还原自 Swoole Compiler 加密文件: WxpayServiceProvider.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/complaint/WxpayServiceProvider.php

namespace app\admin\controller\complaint;

class WxpayServiceProvider extends \app\common\controller\Backend {
    protected ?\app\admin\model\Complaint $complaint; // [opcode 对照] 可空：initialize 中 find() 结果直接赋值且随后 empty() 检查
    protected \app\admin\model\EntryChannel $entryChannel;
    protected \app\common\library\complaint\WxpayServiceProvider $apiClass;
    public function initialize(): void
    {
        // opcode: GET_CALLED_CLASS(initialize)+DO_FCALL=parent::initialize(); QM_ASSIGN 结果按普通赋值重建
        parent::initialize();
        // opcode: DECLARE_CONST(request)+YIELD_FROM(param)+NOP('id')+DO_FCALL+QM_ASSIGN
        $id = request()->param('id');
        if (empty($id)) {
            $this->error('ID为空');
        }
        // opcode: GET_CALLED_CLASS(Complaint,find)+SEND($id)+ASSIGN_OBJ(complaint)
        // [opcode 对照] find() 可能返回 null，空检查+error('投诉记录不存在') 证明原属性可空，降级件丢了 '?'（同 PLAYBOOK 签名规则）
        $this->complaint = \app\admin\model\Complaint::find($id);
        if (empty($this->complaint)) {
            $this->error('投诉记录不存在');
        }
        // opcode: GET_CALLED_CLASS(EntryChannel,find)+FETCH_OBJ(complaint.ec_id)+QM_ASSIGN
        $entryChannel = \app\admin\model\EntryChannel::find($this->complaint->ec_id);
        if (empty($entryChannel)) {
            $this->error('投诉单对应的服务商配置不存在');
        }
        $this->entryChannel = $entryChannel;
        // opcode: NEW(library)+SEND($entryChannel)+ASSIGN_OBJ(apiClass)
        $this->apiClass = new \app\common\library\complaint\WxpayServiceProvider($entryChannel);
    }



    public function getcomplaintinfo(): void
    {
        // opcode: DECLARE_CONST(apiClass)+YIELD_FROM(getComplaintDetail)+DECLARE_CONST(complaint/complaint_no)
        //         +INIT_ARRAY('complaint_id')+NOP(保护层 SEND)+DO_FCALL+ASSIGN
        try {
            $params = $this->apiClass->getcomplaintdetail([
                'complaint_id' => $this->complaint->complaint_no,
            ]);
        // opcode: CATCH(Exception) + YIELD_FROM(error/getMessage) + DO_FCALL + RETURN
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        // opcode: YIELD_FROM(success)+NOP('OK')+SEND($params,2)+DO_FCALL+RETURN
        $this->success('OK', $params);
    }


    public function getcomplainthistory(): void
    {
        // opcode: DECLARE_CONST(request)+YIELD_FROM(param)+NOP(数组字面量)+DO_FCALL+ASSIGN
        // 字面量真值（打包数组偶数槽）: ['limit', 'offset']（奇数槽为编译器清零哑桶，raw arData 实证）
        $params = request()->param(['limit', 'offset']);
        try {
            // opcode: FETCH_DIM_IS+COALESCE(50) / FETCH_DIM_IS+COALESCE(0) + SEND_FUNC_ARG
            $result = $this->apiClass->getcomplainthistory(
                $this->complaint->complaint_no,
                $params['limit'] ?? 50,
                $params['offset'] ?? 0
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('OK', $result);
    }


    public function replycomplaint(): void
    {
        // opcode: request()+param 数组字面量（raw arData n=1 实证: ['response_content']）
        $params = request()->param(['response_content']);
        try {
            // opcode: YIELD_FROM(reply) + FETCH_OBJ(complaint.complaint_no / complaint.merchant.merchant_id) + FETCH_DIM(response_content)
            $this->apiClass->reply(
                $this->complaint->complaint_no,
                $this->complaint->merchant->merchant_id,
                $params['response_content']
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('回复成功');
    }


    public function markcomplete(): void
    {
        try {
            // opcode: YIELD_FROM(handleComplete) + SEND(complaint_no) + SEND(merchant.merchant_id) + DO_FCALL
            $this->apiClass->handlecomplete(
                $this->complaint->complaint_no,
                $this->complaint->merchant->merchant_id
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('标记完成成功');
    }


    public function updaterefundapproval(): void
    {
        // opcode: param 数组字面量 raw arData 实证: ['action', 'launch_refund_day', 'reject_reason', 'remark']
        $params = request()->param(['action', 'launch_refund_day', 'reject_reason', 'remark']);
        try {
            // opcode: SEND(complaint_no) + FETCH_DIM(action) + SEND_VAR($params, 3)
            $this->apiClass->updaterefundprogress(
                $this->complaint->complaint_no,
                $params['action'],
                $params
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('更新成功');
    }


    public function queryimage(): \think\Response
    {
        // opcode: request()+param('url')+ASSIGN
        $url = request()->param('url');
        try {
            // opcode: YIELD_FROM(queryImage)+SEND($url)+DO_FCALL+ASSIGN
            $image = $this->apiClass->queryimage($url);
        // opcode: CATCH(Exception)+CONCAT('出现错误：',getMessage)+DO_FCALL(error)
        } catch (\Exception $e) {
            $this->error('出现错误：' . $e->getMessage());
            // opcode: try 体 JMP(按 (idx*32+op1)/32) 越过此处直达 137 行路径；
            // error() 抛 HttpResponseException，此 return 为原始代码保留的死代码（其后 RETURN 位于 line=135，正常路径在 line=137）
            return \think\Response::create('')->content('image/png');
        }
        // opcode: GET_CALLED_CLASS(think\Response,create)+SEND($image)+YIELD_FROM(header)
        //         +INIT_ARRAY('Content-Type'=>'image')+DO_FCALL_BY_NAME(strlen)+ADD_ARRAY_ELEMENT(Content-Length/Cache-Control)
        return \think\Response::create($image)->header([
            'Content-Type'   => 'image',            // [opcode 对照] 字面量真值 LIT28='image'（非 'image/png'，后者属 catch 死代码）
            'Content-Length' => strlen($image),
            'Cache-Control'  => 'max-age=86400',
        ]);
    }


}

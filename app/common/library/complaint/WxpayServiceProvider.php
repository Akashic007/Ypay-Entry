<?php

// 还原自 Swoole Compiler 加密文件: WxpayServiceProvider.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/complaint/WxpayServiceProvider.php

namespace app\common\library\complaint;

class WxpayServiceProvider implements \app\common\service\complaint\ComplaintInterface {
    protected \WeChatPay\BuilderChainable $instance;
    protected \app\admin\model\EntryChannel $channel;

    public function __construct(\app\admin\model\EntryChannel $channel)
    {
        // opcode: NEW(RequestTool)+SEND_FUNC_ARG($channel->config)+DO_FCALL+ASSIGN；
        //         YIELD_FROM(getInstance)+DO_FCALL+ASSIGN_OBJ(instance)
        try {
            $requestTool = new \app\common\library\wechat\RequestTool($channel->config);
            $this->instance = $requestTool->getinstance();
        // opcode: CATCH(Exception)+YIELD_FROM(getMessage)+NEW(ba\Exception)+SEND+DO_FCALL+THROW
        } catch (\Exception $e) {
            throw new \ba\Exception($e->getMessage());
        }
        // opcode: ASSIGN_OBJ(channel)+[-1](值 $channel)+RETURN
        $this->channel = $channel;
    }

    public function getcomplaintlist(array|null $params = null): array
    {
        // opcode: RECV(params)+RETURN(<arr>{}) —— 原方法体仅返回空数组
        return [];
    }

    public function getcomplaintdetail(array|null $params = null): array
    {
        // opcode: FETCH_DIM(params,'complaint_id')+ASSIGN
        $complaint_id = $params['complaint_id'];
        try {
            // opcode: DECLARE_CONST(instance)+YIELD_FROM(chain)+FAST_CONCAT('/v3/merchant-service/complaints-v2/'.complaint_id)
            //         +DO_FCALL+YIELD_FROM(get)+DO_FCALL+ASSIGN(response)
            $response = $this->instance->chain('/v3/merchant-service/complaints-v2/' . $complaint_id)->get();
        // opcode: CATCH(Exception)+INSTANCEOF(RequestException)+hasResponse→getResponse→json_decode(getBody)
        //         →NEW(ba\Exception($data->message ?? '投诉单查询异常'))+THROW；否则 NEW(ba\Exception($e->getMessage()))+THROW
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data = json_decode($response->getBody());
                throw new \ba\Exception($data->message ?? '投诉单查询异常');
            }
            throw new \ba\Exception($e->getMessage());
        }
        // opcode: IS_NOT_EQUAL(getStatusCode(),200)+JMPZ→52 ‖ IS_NOT_EQUAL(getStatusCode(),'200')+JMPZ→52
        //         →NEW(ba\Exception('查询错误'))+THROW
        if ($response->getStatusCode() !== 200 || $response->getStatusCode() != '200') {
            throw new \ba\Exception('查询错误');
        }
        // opcode: json_decode(getBody()->getContents(), true)+ASSIGN(response)
        $response = json_decode($response->getBody()->getContents(), true);
        // opcode: ISSET_ISEMPTY_DIM_OBJ(complaint_id,EMPTY)+JMPNZ→65 ‖ ISSET_ISEMPTY_DIM_OBJ(complaint_state,EMPTY)+JMPZ→69
        //         →NEW(ba\Exception('投诉单查询失败'))+THROW
        if (empty($response['complaint_id']) || empty($response['complaint_state'])) {
            throw new \ba\Exception('投诉单查询失败');
        }
        // opcode: GET_CALLED_CLASS(Merchant)+where('merchant_id',complainted_mchid)+find+ASSIGN
        $merchant = \app\admin\model\merchant\Merchant::where('merchant_id', $response['complainted_mchid'])->find();
        // opcode: ISSET_ISEMPTY_CV(merchant,EMPTY)+JMPZ→84 → NEW(ba\Exception('商户不存在当前系统中'))+THROW
        if (empty($merchant)) {
            throw new \ba\Exception('商户不存在当前系统中');
        }
        // opcode: ISSET_ISEMPTY_DIM_OBJ(complaint_order_info[0].out_trade_no,EMPTY)+JMPZ→92 → THROW('投诉对应的订单不存在')
        if (empty($response['complaint_order_info'][0]['out_trade_no'])) {
            throw new \ba\Exception('投诉对应的订单不存在');
        }
        // opcode: GET_CALLED_CLASS(Order)+where('order_no',complaint_order_info[0].out_trade_no)+find+ASSIGN
        $order = \app\admin\model\Order::where('order_no', $response['complaint_order_info'][0]['out_trade_no'])->find();
        // opcode: MATCH(complaint_state) 跳转表 {'PROCESSING':32,'PROCESSED':96}+default(608)：
        //         PROCESSING→1 / PROCESSED→2 / default→0
        $state = match ($response['complaint_state']) {
            'PROCESSING' => 1,
            'PROCESSED'  => 2,
            default      => 0,
        };
        // opcode: ASSIGN(wait_reply,0)+ISSET_ISEMPTY_DIM_OBJ(incoming_user_response,ISSET)+in_array(...)
        //         字面量 lit88={0:'true',1:RAW0,2:True,3:RAW0} 打包数组偶数槽真值 → ['true', true]（奇数槽为编译器清零哑桶，
        //         同 admin/controller/complaint/WxpayServiceProvider param(['limit','offset']) 先例）
        $wait_reply = 0;
        if (isset($response['incoming_user_response']) && in_array($response['incoming_user_response'], ['true', true])) {
            // opcode: in_array 真 → ASSIGN(wait_reply,1)
            $wait_reply = 1;
        }
        // opcode: GET_CALLED_CLASS(Complaint)+where('complaint_no',complaint_id)+find+ASSIGN
        $complaint = \app\admin\model\Complaint::where('complaint_no', $response['complaint_id'])->find();
        if (empty($complaint)) {
            // opcode: INIT_ARRAY/ADD_ARRAY_ELEMENT 108-122 行；
            //         ']T' 经 keyB 解出 'id'（merchant_id=$merchant->id / ec_id=$this->channel->id），
            //         user_id/merchant_id 取自 $merchant(CV176)，ec_id/sp_id 取自 $this->channel(CV 字面量 'channel')
            $data = [
                'platform'         => 'wxpay',
                'complaint_no'     => $response['complaint_id'],
                'user_id'          => $merchant->user_id,
                'merchant_id'      => $merchant->id,
                'ec_id'            => $this->channel->id,
                'sp_id'            => $this->channel->sp_id,
                'is_refund'        => (isset($response['problem_type']) && $response['problem_type'] == 'REFUND') ? 1 : 0,
                'complaint_title'  => $response['problem_description'],
                'complaint_detail' => $response['complaint_detail'],
                'order_no'         => empty($order) ? '' : $order->order_no,
                'trade_no'         => empty($order) ? '' : $order->trade_no,
                'status'           => $state,
                'wait_reply'       => $wait_reply,
                'res_content'      => $response,
                // [opcode 对照] 原调用字面量 keyA 解出 'convertisototimestams'（小写 21 字节）；以 keyA 反加密
                // 'convertisototimestamp' 与 raw 仅末字节差 1 bit（'p'/'s'，密钥尾部劣化），PHP 函数名大小写不敏感，
                // 运行时唯一真值 = app/common.php 的 convertIsoToTimestamp()
                'complaint_time'   => convertIsoToTimestamp($response['complaint_time']),
            ];
            // opcode: GET_CALLED_CLASS(Complaint)+create($data)+JMP op1=928 → (185*32+928)/32=214（跳到 action_type 检查）
            \app\admin\model\Complaint::create($data);
        } else {
            // opcode: YIELD_FROM(save)+127-134 行字段+DO_FCALL
            $complaint->save([
                'complaint_title'  => $response['problem_description'],
                'complaint_detail' => $response['complaint_detail'],
                'status'           => $state,
                'wait_reply'       => $wait_reply,
                'res_content'      => $response,
                'complaint_time'   => convertIsoToTimestamp($response['complaint_time']),
                'order_no'         => empty($order) ? '' : $order->order_no,
                'trade_no'         => empty($order) ? '' : $order->trade_no,
            ]);
        }
        // opcode: ISSET_ISEMPTY_DIM_OBJ(params.action_type,ISSET)+JMPZ→219 ‖ IS_EQUAL('REFUND_SUCCESS')+JMPNZ→221
        //         ‖ FETCH_DIM_R(response.complaint_full_refunded)+JMPZ→(220*32+1248)/32=259（跳过退款块）
        if ((isset($params['action_type']) && $params['action_type'] == 'REFUND_SUCCESS') || $response['complaint_full_refunded']) {
            // opcode: GET_CALLED_CLASS(OrderService)+generateOrderNo('c')+ASSIGN（字面量 'R' keyA 解出 'c'）
            $refund_no = \app\common\service\OrderService::generateOrderNo('c');
            // opcode: INIT_ARRAY/ADD_ARRAY_ELEMENT 144-156 行（user_id/id/channel_id/order_no 均取自 $order，CV 字面量 op2=192）
            $refund_data = [
                'user_id'         => $order->user_id,
                'order_id'        => $order->id,
                'channel_id'      => $order->channel_id,
                'order_no'        => $order->order_no,
                'refund_no'       => $refund_no,
                'refund_trade_no' => $complaint_id,
                'order_amount'    => $response['complaint_order_info'][0]['amount'],
                'refund_amount'   => $response['complaint_order_info'][0]['amount'],
                'refund_channel'  => $response['channel'],
                'refund_account'  => '原路退款',
                'refund_reason'   => '投诉订单同意退款',
                'status'          => 1,
                'refund_time'     => time(),
            ];
            // opcode: GET_CALLED_CLASS(RefundRecord)+create($refund_data)
            \app\admin\model\RefundRecord::create($refund_data);
            // opcode: ASSIGN_OBJ(order.status,2)+YIELD_FROM(save)+DO_FCALL
            $order->status = 2;
            $order->save();
        }
        // opcode: VERIFY_RETURN_TYPE+RETURN(response)
        return $response;
    }

    public function getcomplainthistory(string|null $complaint_id = null, int|null $limit = null, int|null $offset = null): array
    {
        // opcode: CONCAT('?limit='.limit)+CONCAT(.&offset=)+CONCAT(.offset)+ASSIGN(query)
        $query = '?limit=' . $limit . '&offset=' . $offset;
        try {
            // opcode: ROPE_INIT('/v3/merchant-service/complaints-v2/')+ROPE_ADD(complaint_id)+ROPE_END('/negotiation-historys')
            //         +CONCAT(.query)+DO_FCALL+YIELD_FROM(get)+DO_FCALL+ASSIGN(response)
            $response = $this->instance->chain('/v3/merchant-service/complaints-v2/' . $complaint_id . '/negotiation-historys' . $query)->get();
        // opcode: CATCH(Exception)+INSTANCEOF(RequestException)+hasResponse→getResponse→json_decode(getBody)
        //         →throw($data->message ?? '投诉单查询异常')；否则 throw($e->getMessage())
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data = json_decode($response->getBody());
                throw new \ba\Exception($data->message ?? '投诉单查询异常');
            }
            throw new \ba\Exception($e->getMessage());
        }
        // opcode: json_decode(getBody()->getContents(), true)+VERIFY_RETURN_TYPE+RETURN
        return json_decode($response->getBody()->getContents(), true);
    }

    public function reply(string|null $complaint_id = null, string|null $mch_id = null, string|null $response_content = null, array|null $params = null): void
    {
        // opcode: ISSET_ISEMPTY_CV(complaint_id,EMPTY)+JMPNZ→8 ‖ ISSET_ISEMPTY_CV(mch_id,EMPTY)+JMPZ→12 → THROW('参数错误')
        if (empty($complaint_id) || empty($mch_id)) {
            throw new \ba\Exception('参数错误');
        }
        // opcode: ISSET_ISEMPTY_CV(response_content,EMPTY)+JMPZ→18 → THROW('请填写回复内容')
        if (empty($response_content)) {
            throw new \ba\Exception('请填写回复内容');
        }
        try {
            // opcode: ROPE('/v3/merchant-service/complaints-v2/'.complaint_id.'/response')+YIELD_FROM(post)
            //         +INIT_ARRAY(mch_id=>complainted_mchid)+ADD(response_content)+INIT_ARRAY(json)+ADD(headers)+DO_FCALL
            $this->instance->chain('/v3/merchant-service/complaints-v2/' . $complaint_id . '/response')->post([
                'json' => [
                    'complainted_mchid' => $mch_id,
                    'response_content'  => $response_content,
                ],
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
            ]);
        // opcode: CATCH(Exception)+INSTANCEOF+hasResponse→getResponse→json_decode(getBody)
        //         →throw($data->message ?? '投诉回复异常')；否则 throw($e->getMessage())
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data = json_decode($response->getBody());
                throw new \ba\Exception($data->message ?? '投诉回复异常');
            }
            throw new \ba\Exception($e->getMessage());
        }
        // opcode: RETURN(null)（line 220，try 体之后、catch 体之前的公共出口）
    }

    public function handlecomplete(string|null $complaint_id = null, string|null $mch_id = null): void
    {
        try {
            // opcode: ROPE('/v3/merchant-service/complaints-v2/'.complaint_id.'/complete')+YIELD_FROM(post)
            //         +INIT_ARRAY(mch_id=>complainted_mchid)+INIT_ARRAY(json)+ADD(headers)+DO_FCALL
            $this->instance->chain('/v3/merchant-service/complaints-v2/' . $complaint_id . '/complete')->post([
                'json'    => ['complainted_mchid' => $mch_id],
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
            ]);
        // opcode: CATCH(Exception)+INSTANCEOF+hasResponse→getResponse→json_decode(getBody)
        //         →throw($data->message ?? '反馈完成异常')；否则 throw($e->getMessage())
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data = json_decode($response->getBody());
                throw new \ba\Exception($data->message ?? '反馈完成异常');
            }
            throw new \ba\Exception($e->getMessage());
        }
        // opcode: RETURN(null)（line 249 公共出口）
    }

    public function updaterefundprogress(string|null $complaint_id = null, string|null $action = null, array|null $params = null): void
    {
        // opcode: ISSET_ISEMPTY_CV(action,EMPTY)+JMPZ→12 → THROW('请选择处理结果')
        if (empty($action)) {
            throw new \ba\Exception('请选择处理结果');
        }
        // opcode: IS_NOT_EQUAL('REJECT')+JMPZ→17 ‖ IS_NOT_EQUAL('APPROVE')+JMPZ→17 → THROW('处理操作错误')
        if ($action !== 'REJECT' && $action !== 'APPROVE') {
            throw new \ba\Exception('处理操作错误');
        }
        // opcode: IS_EQUAL(action,'APPROVE')+JMPZ→27
        if ($action == 'APPROVE') {
            // opcode: ISSET_ISEMPTY_DIM_OBJ(launch_refund_day,EMPTY)+JMPZ→23 → ASSIGN_DIM(0)
            if (empty($params['launch_refund_day'])) {
                $params['launch_refund_day'] = 0;
            }
            // opcode: ISSET_ISEMPTY_DIM_OBJ(reject_reason,EMPTY)+JMPZ→27 → ASSIGN_DIM('默认拒绝退款')
            if (empty($params['reject_reason'])) {
                $params['reject_reason'] = '默认拒绝退款';
            }
        }
        // opcode: INIT_ARRAY(action=>action)+ASSIGN(request_data)
        $request_data = ['action' => $action];
        // opcode: ISSET_ISEMPTY_DIM_OBJ(launch_refund_day,EMPTY)+JMPNZ→34 → ASSIGN_DIM($request_data[...])
        if (!empty($params['launch_refund_day'])) {
            $request_data['launch_refund_day'] = $params['launch_refund_day'];
        }
        // opcode: 同上 reject_reason
        if (!empty($params['reject_reason'])) {
            $request_data['reject_reason'] = $params['reject_reason'];
        }
        // opcode: 同上 remark
        if (!empty($params['remark'])) {
            $request_data['remark'] = $params['remark'];
        }
        try {
            // opcode: ROPE('/v3/merchant-service/complaints-v2/'.complaint_id.'/update-refund-progress')+YIELD_FROM(post)
            //         +INIT_ARRAY(request_data=>json)+ADD(headers)+DO_FCALL
            $this->instance->chain('/v3/merchant-service/complaints-v2/' . $complaint_id . '/update-refund-progress')->post([
                'json'    => $request_data,
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
            ]);
        // opcode: CATCH(Exception)+INSTANCEOF+hasResponse→getResponse→json_decode(getBody)
        //         →throw($data->message ?? '反馈完成异常')；否则 throw($e->getMessage())
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $data = json_decode($response->getBody());
                throw new \ba\Exception($data->message ?? '反馈完成异常');
            }
            throw new \ba\Exception($e->getMessage());
        }
        // opcode: RETURN(null)（line 292 公共出口）
    }

    public function setcallbackurl(string|null $method = null): void
    {
        // opcode: request()+YIELD_FROM(domain)+CONCAT('/notify/complaint/wxpayServiceProvider/')
        //         +DECLARE_CONST(channel)+DECLARE_CONST(']T' keyB='id')+CONCAT+ASSIGN(url)
        $url = request()->domain() . '/notify/complaint/wxpayServiceProvider/' . $this->channel->id;
        try {
            // opcode: YIELD_FROM(callbackUrl) ext=2+UNSET_OBJ('tuc' 3字节→'GET')+-1(SEND [],2)+DO_FCALL+ASSIGN(result)
            $result = $this->callbackurl('GET', []);
            // opcode: ISSET_ISEMPTY_DIM_OBJ(result.url,EMPTY)+JMPNZ op2=1536→(16*32+1536)/32=64
            //         ‖ IS_NOT_EQUAL(url,result.url)+JMPZ op2=1440→64
            if (!empty($result['url']) && $result['url'] != $url) {
                // opcode: INIT_ARRAY(url=>url)/INIT_ARRAY(json)/ADD_ARRAY_ELEMENT(headers)+ASSIGN(post_data)（307-309 行）
                $post_data = ['json' => ['url' => $url], 'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json']];
                // opcode: YIELD_FROM(callbackUrl) ext=2+UNSET_OBJ('cec' 3字节→'PUT')+SEND_VAR(post_data,2)+DO_FCALL+ASSIGN
                $result = $this->callbackurl('PUT', $post_data);
            }
        // opcode: JMP op1=1120→(29*32+1120)/32=64 为 try 体出口；CATCH(ba\Exception)
        } catch (\ba\Exception $e) {
            // opcode: getMessage+IS_EQUAL('数据查询结果为空')+JMPNZ→39 ‖ IS_EQUAL('\u6570…'原文转义串)+JMPZ→(38*32+608)/32=57
            if ($e->getMessage() == '数据查询结果为空' || $e->getMessage() == '\u6570\u636e\u67e5\u8be2\u7ed3\u679c\u4e3a\u7a7a') {
                // opcode: INIT_ARRAY/ADD 321-323 行（catch 内重建 post_data，GET 先行抛错时尚未构建）
                $post_data = ['json' => ['url' => $url], 'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json']];
                try {
                    // opcode: YIELD_FROM(callbackUrl) ext=2+UNSET_OBJ('d\x7fjb' 4字节→'POST')+SEND_VAR(post_data,2)+DO_FCALL+ASSIGN
                    $result = $this->callbackurl('POST', $post_data);
                // opcode: JMP op1=512→(48*32+512)/32=64 为内层 try 体出口；CATCH(ba\Exception)
                } catch (\ba\Exception $e) {
                    // opcode: NEW(ba\Exception)+CONCAT('创建回调地址失败: '.getMessage)+DO_FCALL+THROW
                    throw new \ba\Exception('创建回调地址失败: ' . $e->getMessage());
                }
            } else {
                // opcode: NEW(Exception)+CONCAT('设置回调地址失败:'.getMessage)+DO_FCALL+THROW
                throw new \Exception('设置回调地址失败:' . $e->getMessage());
            }
        }
        // opcode: ISSET_ISEMPTY_DIM_OBJ(result.url,EMPTY)+JMPNZ op2=416→(65*32+416)/32=78(RETURN)
        if (!empty($result['url'])) {
            // opcode: DECLARE_CONST(channel)+DECLARE_CONST(config)+ASSIGN(config)
            //         +FETCH_DIM_R(result.url)+ASSIGN_DIM(complaint_callback_url)
            //         +FETCH_OBJ_W(channel)+ASSIGN_OBJ(config)+DECLARE_CONST(channel)+YIELD_FROM(save)+DO_FCALL
            $config                            = $this->channel->config;
            $config['complaint_callback_url'] = $result['url'];
            $this->channel->config             = $config;
            $this->channel->save();
        }
        // opcode: RETURN(null)（line 346）
    }

    public function delcallbackurl(): void
    {
        try {
            // opcode: ASSIGN(headers,{'headers':{'Accept':'application/json'}})（line 355）
            $headers = ['headers' => ['Accept' => 'application/json']];
            // opcode: YIELD_FROM(callbackUrl) ext=2+UNSET_OBJ('rtxq~\x05' 6字节→'DELETE')+SEND_VAR(headers,2)+DO_FCALL
            $this->callbackurl('DELETE', $headers);
        // opcode: CATCH(ba\Exception)+NEW(ba\Exception)+CONCAT('删除回调地址失败:'.getMessage)+THROW
        } catch (\ba\Exception $e) {
            throw new \ba\Exception('删除回调地址失败:' . $e->getMessage());
        }
        // opcode: DECLARE_CONST(channel)+DECLARE_CONST(config)+ASSIGN(config)
        //         +ASSIGN_DIM(complaint_callback_url,'')+FETCH_OBJ_W+ASSIGN_OBJ+YIELD_FROM(save)+DO_FCALL
        $config                            = $this->channel->config;
        $config['complaint_callback_url'] = '';
        $this->channel->config             = $config;
        $this->channel->save();
    }

    public function getcallbackurl(): void
    {
        // opcode: YIELD_FROM(callbackUrl) ext=2+UNSET_OBJ('tuc' 3字节→'GET')+-1(SEND {'headers':{'Accept':'application/json'}},2)
        //         +DO_FCALL；结果弃用（void），RETURN null
        $this->callbackurl('GET', ['headers' => ['Accept' => 'application/json']]);
    }

    private function callbackurl(string|null $method = null, array|null $data = null): array
    {
        try {
            // opcode: DECLARE_CONST(instance)+YIELD_FROM(chain)+NOP('/v3/merchant-service/complaint-notifications')
            //         +DO_FCALL+ASSIGN(client)
            $client = $this->instance->chain('/v3/merchant-service/complaint-notifications');
            // opcode: MATCH(method) 跳转表 {'GET':32,'POST':160,'PUT':320,'DELETE':480}+default(608)：
            //         GET→client->get() / POST→client->post($data) / PUT→client->put($data) / DELETE→client->delete()
            //         / default→THROW('无效的操作')（PHP8 throw 表达式）
            $result = match ($method) {
                'GET'    => $client->get(),
                'POST'   => $client->post($data),
                'PUT'    => $client->put($data),
                'DELETE' => $client->delete(),
                default  => throw new \ba\Exception('无效的操作'),
            };
        // opcode: JMP op1=832→(32*32+832)/32=58 为 try 体出口；CATCH(Exception)+INSTANCEOF+hasResponse
        //         →getResponse→json_decode(getBody)→FETCH_OBJ(message)+ASSIGN(msg)；否则 ASSIGN(msg,getMessage)；
        //         NEW(ba\Exception($msg))+THROW
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                $result   = json_decode($response->getBody());
                $msg      = $result->message;
            } else {
                $msg = $e->getMessage();
            }
            throw new \ba\Exception($msg);
        }
        // opcode: SPACESHIP(json_decode 回退名)+YIELD_FROM(getBody)+YIELD_FROM(getContents)+SEND+NOP(true)
        //         +DO_FCALL_BY_NAME+COALESCE+[-1]({}) → json_decode(...,true) ?? []；VERIFY_RETURN_TYPE+RETURN
        return json_decode($result->getBody()->getContents(), true) ?? [];
    }

    public function uploadimage()
    {
        // opcode: 原方法体仅 RETURN null（无其他指令），保守保留空实现
    }

    public function queryimage(string|null $url = null): string
    {
        // opcode: SPACESHIP(parse_url 回退名, ext=2)+SEND(url,1)+FETCH_CONSTANT(PHP_URL_PATH)+DO_FCALL_BY_NAME+ASSIGN(path)
        $path     = parse_url($url, PHP_URL_PATH);
        // opcode: SPACESHIP(basename 回退名, ext=1)+SEND(path,1)+DO_FCALL_BY_NAME+ASSIGN(media_id)
        $media_id = basename($path);
        // opcode: SPACESHIP(urldecode 回退名, ext=1)+SEND(media_id,1)+DO_FCALL_BY_NAME+ASSIGN(media_id)
        $media_id = urldecode($media_id);
        try {
            // opcode: DECLARE_CONST 链 instance→'B\x03'(2字节='v3')→'merchantService'→'images'→'_media_id_'（Builder 规范化
            //         '#^_(.*)_$#'→'{media_id}' 占位段，由 options.media_id 替换，见 vendor/wechatpay/src/Builder.php）
            //         +YIELD_FROM(get) ext=1+INIT_ARRAY({'Accept':'*/*'}=>headers)+ADD_ARRAY_ELEMENT(media_id=>media_id)+DO_FCALL
            $response = $this->instance->v3->merchantService->images->_media_id_->get([
                'headers'  => ['Accept' => '*/*'],
                'media_id' => $media_id,
            ]);
        // opcode: JMP op1=288→(26*32+288)/32=35 为 try 体出口；CATCH(Exception)+NEW(ba\Exception)
        //         +CONCAT('图片请求失败：'.getMessage)+THROW
        } catch (\Exception $e) {
            throw new \ba\Exception('图片请求失败：' . $e->getMessage());
        }
        // opcode: YIELD_FROM(getBody)+YIELD_FROM(getContents)+DO_FCALL+VERIFY_RETURN_TYPE+RETURN
        return $response->getBody()->getContents();
    }

}

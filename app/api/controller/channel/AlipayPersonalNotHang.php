<?php

// 还原自 Swoole Compiler 加密文件: AlipayPersonalNotHang.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/channel/AlipayPersonalNotHang.php

namespace app\api\controller\channel;

class AlipayPersonalNotHang extends \app\common\controller\Frontend {
    private \app\admin\model\channel\Account $channel;
    public function initialize(): void
    {
        // [opcode 还原] line17 parent::initialize(); line18 $id=request param('id')
        // line19 通道ID为空 / line20 Account::find / line21 通道不存在
        // line22 channel_type != 'alipay_personal_not_hang' → 通道类型错误
        parent::initialize();
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('通道ID为空');
        }
        $this->channel = \app\admin\model\channel\Account::find($id);
        if (empty($this->channel)) {
            $this->error('通道不存在');
        }
        if ($this->channel->channel_type != 'alipay_personal_not_hang') {
            $this->error('通道类型错误');
        }
    }

    public function getloginqrcode(): void
    {
        // [opcode 还原] line32 实例化 payChannel 库类; line33 getLoginQrcode();
        // line34-35 config=['loginId'=>...]; line38 save; line40-41 success(ok,[loginId,qr_url])
        $obj = new \app\common\library\payChannel\AlipayPersonalNotHang($this->channel);
        $result = $obj->getLoginQrcode();
        $this->channel->config = [
            'loginId' => $result['loginId'],
        ];
        $this->channel->save();
        $this->success('ok', [
            'loginId' => $result['loginId'],
            'qr_url'  => '/qrcode?data=' . $result['qrcodeUrl'],
        ]);
    }

    public function getloginstatus(): void
    {
        // [opcode 还原] line50 实例化; line52 loginId为空; line53 getLoginStatus(loginId);
        // line54-55 code!=1 → success(msg,[],2)(result 抛 HttpResponseException 中止);
        // line57 $pid=getsubstr(base64_decode(cookie),'CLUB_ALIPAY_COM=',';');
        // line58 PID获取失败; line59-61+63 $config 写回 loginId/cookie/pid; line62 online_status=1;
        // line64 save; line65 result['pid']=pid; line66 success(ok,result)
        $obj = new \app\common\library\payChannel\AlipayPersonalNotHang($this->channel);
        if (empty($this->channel->config['loginId'])) {
            $this->error('loginId为空');
        }
        $result = $obj->getLoginStatus($this->channel->config['loginId']);
        if ($result['code'] != 1) {
            $this->success($result['msg'], [], 2);
        }
        $pid = getsubstr(base64_decode($this->channel->config['cookie']), 'CLUB_ALIPAY_COM=', ';');
        if (empty($pid)) {
            $this->error('PID获取失败，请重试');
        }
        $config = $this->channel->config;
        $config['loginId'] = $result['loginId'];
        $config['cookie']  = $result['cookie'];
        $config['pid']     = $pid;
        $this->channel->online_status = 1;
        $this->channel->config = $config;
        $this->channel->save();
        $result['pid'] = $pid;
        $this->success('ok', $result);
    }

    public function getmoney(): void
    {
        // [opcode 还原] line71 实例化; line72 cookie为空; line73-74 base64_decode(config['cookie']);
        // line76 getBalance(cookie) 返回字符串余额; line79 success(ok,['money'=>余额])
        $obj = new \app\common\library\payChannel\AlipayPersonalNotHang($this->channel);
        if (empty($this->channel->config['cookie'])) {
            $this->error('cookie为空');
        }
        $cookie = base64_decode($this->channel->config['cookie']);
        $result = $obj->getBalance($cookie);
        $this->success('ok', ['money' => $result]);
    }

}

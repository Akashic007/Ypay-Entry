<?php

// 还原自 Swoole Compiler 加密文件: AlipayPersonalNotHang.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/AlipayPersonalNotHang.php

namespace app\common\library\payChannel;

class AlipayPersonalNotHang implements \app\common\service\channel\contract\AlipayPersonalNotHangInterface {
    protected \app\admin\model\channel\Account $account;
    // [opcode 对照] 常量值按 payload 常量表真值补回（打包数组取偶数槽真值，奇数槽为编译器清零哑桶）
    const QUERY_ENDPOINTS = [
        'primary' => [
            'https://my.alipay.com/portal/i.htm?src=yy_taobao_gl_01&sign_from=3000&sign_account_no=&guide_q_control=top',
            'https://zht.alipay.com/asset/newIndex.htm',
            'https://shenghuo.alipay.com/send/payment/fill.htm?_pdType=adbhajcaccgejhgdaeih',
        ],
        'fallback' => [
            'https://personalweb.alipay.com/portal/i.htm',
            'https://my.alipay.com/wealth/index.html',
            'https://110.alipay.com/sc/index.htm',
            'https://my.alipay.com/portal/i.htm',
            'https://shanghu.alipay.com/home/switchPersonal.htm',
        ],
    ];
    const USER_AGENTS = [
        'Mozilla/5.0 (Linux; Android 10.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36 360Browser/9.2.5584.400',
    ];
    public function __construct(\app\admin\model\channel\Account $account)
    {
        $this->account = $account;
    }
    public function pay(\app\admin\model\Order|null $order = null): \app\common\service\channel\PayInfo
    {
        if (empty($this->account->config['pid'])) {
            throw new \ba\Exception('PID为空');
        }
        $url = request()->domain() . '/views/jump/alipay-jump.php?user_id=' . $this->account->config['pid'] . '&price=' . $order->pay_money . '&trade_no=' . $order->out_trade_no;
        $payUrl = 'alipays://platformapi/startapp?appId=20000067&url=' . urlencode($url);
        return new \app\common\service\channel\PayInfo([
            'qrcode' => $payUrl,
            'h5_url' => $payUrl,
        ]);
    }
    public function callback(\app\admin\model\Order|null $order = null): bool
    {
        return true;
    }
    // [opcode 对照] $scene 默认值按 RECV_INIT op2=LIT('add') 真值补回（原签名 null 为管线丢失）
    public function handleparams(array|null $params = null, string|null $scene = 'add'): void
    {
        if ($scene == 'add') {
            $this->account->online_status = 0;
            $this->account->save();
        }
    }
    public function handledelete(): void
    {
    return;
    }
    public function getloginqrcode(): array
    {
        error_reporting(0);
        $url = 'https://auth.alipay.com/login/index.htm';
        $data = httpcurl($url, 'GET', [], [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko',
            'Accept: image/gif, image/jpeg, image/pjpeg, application/x-ms-application, application/xaml+xml, application/x-ms-xbap, */*',
            'Referer: https://auth.alipay.com/login/index.htm?goto=https%3A%2F%2Fmy.alipay.com%2Fportal%2Fi.htm',
            'Accept-Language: zh-Hans-CN,zh-Hans;q=0.5',
        ]);
        preg_match('/securityId: "(.*?)",/', $data['body'], $match);
        $authCenter_qrcode_login = $match[1];
        preg_match('/s.sid = "(.*?)"/', $data['body'], $match);
        $authCenter_queryPwd_login = $match[1];
        $rds_form_token = getsubstr($data['body'], '<input type="hidden" value="', '" name="rds_form_token"/>');
        $aliEditUid = getsubstr($data['body'], '<input type="hidden" id="alieditUid" name="alieditUid" value="', '" />');
        preg_match('/ALIPAYJSESSIONID=(.*?);/', $data['header'], $match);
        $AliQCode = 'https://qr.alipay.com/_d?_b=PAI_LOGIN_DY&amp;securityId=' . urlencode($authCenter_qrcode_login);
        $array = [
            'loginId' => $AliQCode . 'YPay' . $authCenter_queryPwd_login . 'YPay' . $rds_form_token . 'YPay' . $aliEditUid,
            'qrcodeUrl' => $AliQCode,
        ];
        return $array;
    }
    public function getloginstatus(string|null $loginId = null): array
    {
        error_reporting(0);
        if (empty($loginId)) {
            return ['code' => 0, 'msg' => 'LoginId为空'];
        }
        $YPay = explode('YPay', $loginId);
        $post_intl = [
            'securityId' => $YPay[0],
            '_callback' => 'light.request._callbacks.callback2',
        ];
        $post_intl = http_build_query($post_intl);
        $data_intl = httpcurl('https://securitycore.alipay.com/barcode/barcodeProcessStatus.json?' . $post_intl, 'GET', [], [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko',
            'Accept: image/gif, image/jpeg, image/pjpeg, application/x-ms-application, application/xaml+xml, application/x-ms-xbap, */*',
            'Referer: https://auth.alipay.com/login/index.htm?goto=https%3A%2F%2Fmy.alipay.com%2Fportal%2Fi.htm',
            'Accept-Language: zh-Hans-CN,zh-Hans;q=0.5',
        ]);
        if (300 < $data_intl['status']) {
            $data_intl = httpcurl('https://securitycore.alipay.com/barcode/barcodeProcessStatus.json?securityId=' . $YPay[0] . '&_callback=light.request._callbacks.callback1', 'GET', [], [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 UBrowser/6.2.4094.1 Safari/537.36',
            ], false, 'https://auth.alipay.com/login/index.htm?goto=https%3A%2F%2Fconsumeprod.alipay.com%2Frecord%2Fstandard.htm', '', '', 10, $loginId);
        }
        if (strpos(json_encode($data_intl), 'waiting')) {
            return ['code' => 0, 'msg' => '等待扫描二维码'];
        }
        if (strpos(json_encode($data_intl), 'scanned')) {
            return ['code' => 0, 'msg' => '等待确认中'];
        }
        $url = 'https://authet2.alipay.com/login/index.htm';
        $post = [
            'support' => '000001',
            'needTransfer' => '',
            'CtrlVersion' => '1,1,0,1',
            'loginScene' => 'index',
            'redirectType' => '',
            'personalLoginError' => '',
            'goto' => 'https://www.alipay.com/',
            'errorVM' => '',
            'sso_hid' => '',
            'site' => '',
            'rds_form_token' => $YPay[2],
            'json_tk' => '',
            'method' => 'qrCodeLogin',
            'logonId' => '',
            'superSwitch' => 'true',
            'noActiveX' => 'false',
            'passwordSecurityId' => $YPay[1],
            'qrCodeSecurityId' => $YPay[0],
            'scid' => '',
            'password_input' => '',
            'J_aliedit_using' => 'true',
            'password' => '',
            'J_aliedit_key_hidn' => 'password',
            'J_aliedit_uid_hidn' => 'alieditUid',
            'alieditUid' => $YPay[3],
            'REMOTE_PCID_NAME' => '_seaside_gogo_pcid',
            '_seaside_gogo_pcid' => '',
            '_seaside_gogo_' => '',
            '_seaside_gogo_p' => '',
            'J_aliedit_prod_type' => '',
            'security_activeX_enabled' => 'true',
            'checkCode' => '',
            'idPrefix' => '',
            'preCheckTimes' => 5,
            'ua' => 'dxlTasyfLePewE7ifH7HyT5wJ5cASsGqMaYnOomEiyTBeHyI6CezVWDWcDouca6W6Y4Svep9ulZ8H0cF1X4Mgi.JZTbQL3NddYS7bCmG.cFh45fZXR3kTmjsMi3xByeTW2V5hnaat0y1OOiv8qoAfKgaUuigtJAp3UL2QgUVrpASMRKdStX0h.hzFfH26FHtMkCmnf1nRcw74yljdFFMC03XWUBNZDhPUI0aL76t.NVxaOJQngu.KFQoPrVjSWYgym6MackvvBhmL37Y0s4H.ROLsAdVrDnLoQR7y07wcwWbUSqq.6AdBebIIVg1RHjyn3K9ahqPk_HOBlXyg6_voFZWwvoFlVAZ9c_ARvTidwDCE.18sT9z2ELtWGaAVClk6HN0HXMQUwOH7Rr7sfpn3zp__eOAe75qTBmYMNXnYnChZmqWOAaVdJAcFpjoUtwtwwqcZvdvoX4_UH.06SpF.Z0i85GWt4jSkki5ijEyvav5KeLQX6Tvj7MziuxQasAOVX6CHZu62D3FhWwj1cesYq9iKyzNmhMcqc.ULS88i3oq2vZko8vOI3BFufd0GcYAMI.YS8a4IqoaE4ydLO4ALR.8WuHvpOZiilHq.hOZogZB2QoQApBuo5smKdhzGlcybdYtsoxPtD_jIRXtf7aRzIWtIlcgEHk6RyOhjsA7bSuWurcukkCAvTZtO05xq6s_lmjMUVNeyS34DpiEXKJlqmbi.amq3hj2Oph0AZtvPb8LvZJy8V.aYdcC4RH9UeAOEzzJgpeiiAAUcMRexpGs5ZDcBFdgn4MIXfq2aTIUFVHf0W3in7tGaeNmx1MUIWHHTL_3SmNYyPVaEo5qZzNLTMrc318MBiIFjcTmaub1IJw7IZefBjspVK9bzzYMwhe0ljkUqoCoshjN9rlXjKyJ.vsXLosDb7KEKmWejetEAPRlw2e49JmWJj5ohGrOGzlsTzyMUtY07nlv3gjXWkaUaE',
        ];
        $data = httpcurl($url, 'POST', $post, []);
        $data = $data['headers'];
        $ALIPAYJSESSIONID = explode('Domain=', explode('ALIPAYJSESSIONID=', $data)[2])[0];
        $ctoken = explode('Domain=', explode('ctoken=', $data)[2])[0];
        $CLUB_ALIPAY_COM = explode('Domain=', explode('CLUB_ALIPAY_COM=', $data)[1])[0];
        $JSESSIONID = explode(';', explode('JSESSIONID=', $data)[1])[0];
        $alipay = explode(';', explode('alipay=', $data)[1])[0];
        $spanner = explode(';', explode('spanner=', $data)[1])[0];
        if ($ALIPAYJSESSIONID && $CLUB_ALIPAY_COM) {
            $cookies = 'zone=GZ00C; JSESSIONID=' . $JSESSIONID . '; ali_apache_tracktmp="uid=' . $CLUB_ALIPAY_COM . '"; IntroKey=true; session.cookieNameId=ALIPAYJSESSIONID; ALIPAYJSESSIONID=' . $ALIPAYJSESSIONID . ' ctoken=' . $ctoken . ' CLUB_ALIPAY_COM=' . $CLUB_ALIPAY_COM;
        }
        $array = [];
        $array['cookie'] = base64_encode($cookies);
        return $array;
    }
    public function monitor(): void
    {
        $account = $this->account;
        $output = new \think\console\Output();
        if (empty($account->config['cookie'])) {
            if ($account->online_status == 1) {
                $output->writeln('通道[' . $account->id . ']COOKIE不存在，通道下线');
                $this->offline();
            }
            return;
        }
        $cookie = base64_decode($account->config['cookie']);
        $money = $this->getBalance($cookie);
        if ($money == '-1') {
            $output->writeln('[' . date('Y-m-d H:i:s') . '] 通道[' . $account->id . ']金额获取失败，通道下线');
            $this->offline();
            return;
        }
        if (!is_numeric($money)) {
            $output->writeln('[' . date('Y-m-d H:i:s') . '] 通道[' . $account->id . ']金额获取失败，通道下线');
            $this->offline();
            return;
        }
        $oldMoney = $account->extend['alipay_money'] ?? 0;
        if ($money != $oldMoney) {
            $account->extend = ['alipay_money' => $money];
            $account->save();
            $diffMoney = bcsub($money, $oldMoney, 2);
            $diffMoney = (int) round($diffMoney * 100);
            $output->writeln('[' . date('Y-m-d H:i:s') . '] 通道[' . $account->id . ']金额变动，开始匹配订单');
            try {
                $order = \app\admin\model\Order::where('status', 0)->where('channel_id', $account->id)->where('expire_time', '>', time())->where('pay_money', $diffMoney)->order('id desc')->find();
                if (empty($order)) {
                    $output->writeln('[' . date('Y-m-d H:i:s') . '] 通道[' . $account->id . ']无匹配订单-继续监控');
                    return;
                }
                $output->writeln('[' . date('Y-m-d H:i:s') . '] 通道[' . $account->id . ']匹配订单成功：' . $order->order_no);
                $orderService = new \app\common\service\OrderService();
                $orderService->callback($order);
                $output->writeln('[' . date('Y-m-d H:i:s') . '] 通道[' . $account->id . ']订单回调成功：' . $order->order_no);
            } catch (Exception $e) {
                $output->writeln('[' . date('Y-m-d H:i:s') . '] 通道[' . $account->id . ']订单回调失败：' . $e->getMessage());
            }
        }
    }
    public function offline(): void
    {
        $this->account->online_status = 0;
        $this->account->config = [];
        $this->account->save();
        \think\facade\Event::trigger(new \app\common\event\ChannelNotify($this->account, \app\common\event\ChannelNotify::OFFLINE, ['offline_time' => date('Y-m-d H:i:s')]));
    }
    public function getbalance(string|null $cookie = null): string
    {
        try {
            $url = self::QUERY_ENDPOINTS['primary'][array_rand(self::QUERY_ENDPOINTS['primary'])];
            $result = self::getAlipayCookieBeat($url, $cookie);
            if ($result !== '-1') {
                return $result;
            }
            $url = self::QUERY_ENDPOINTS['fallback'][array_rand(self::QUERY_ENDPOINTS['fallback'])];
            $result = self::getAlipayCookie($url, $cookie);
            return $result;
        } catch (Exception $e) {
            \think\facade\Log::error('支付宝余额查询失败: ' . $e->getMessage());
            return '-1';
        }
    }
    private static function getalipaycookiebeat($url_referer, $cookie): string
    {
        $url = 'https://uemprod.alipay.com/service.json?ctoken=&_input_charset=utf-8&_ksTS=' . microtime(true) . '&operation=mrchcenter.artisan.v2.ext.query&data=%7B%22pageSource%22%3A%22b_site_mrchenter_home_index_route%22%7D';
        $referer = $url_referer . '?&t=' . time();
        $ua = 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8
        accept-encoding: gzip, deflate, br
        accept-language: zh-CN,zh;q=0.9
        cache-control: max-age=0
        Cookie: ' . $cookie . '
        referer: ' . $url . '?referer=' . $referer . '
        upgrade-insecure-requests: 1
        user-agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.80 Safari/537.36 Core/1.47.277.400 QQBrowser/9.4.7658.400';
        $result = self::getMoneyCurlInit($url_referer, 0, $referer, $cookie, 0, $ua);
        $result = self::getMoneyCurlInit('https://lab.alipay.com/user/balance/index.htm', 0, $referer, $cookie, 0);
        $result = mb_convert_encoding($result, 'UTF-8', 'GB2312');
        if (!str_contains($result, '可用余额')) {
            $result = '-1';
            return $result;
        }
        $result = getsubstr($result, '<span class="fm-free">', '</span>');
        return $result;
    }
    private static function getalipaycookie($url_referer, $cookie): string
    {
        $Url = 'https://shanghu.alipay.com/user/myAccount/index.htm';
        $referer = $url_referer . '?&t=' . time();
        $ua = 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8
            accept-encoding: gzip, deflate, br
            accept-language: zh-CN,zh;q=0.9
            cache-control: max-age=0
            Cookie: ' . @$cookie . '
            referer: ' . $Url . '?referer=' . $referer . '
            upgrade-insecure-requests: 1
            user-agent: Mozilla/5.0 (Linux; Android 10.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36 360Browser/9.2.5584.400';
        $result = self::getMoneyCurlInit($url_referer, 0, $referer, $cookie, 0, $ua);
        $result = self::getMoneyCurlInit($Url, 0, $referer, $cookie, 0);
        $result = mb_convert_encoding($result, 'UTF-8', 'GB2312');
        if (!str_contains($result, '余额')) {
            $result = '-1';
            return $result;
        }
        $result = getsubstr($result, '<em class="aside-available-amount">', '</em>元</span></li>');
        return $result;
    }
    private static function getmoneycurlinit($url, $post, $referer, $cookie, $header, $ua, $nobaody): bool|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept:*/*', 'Accept-Encoding:gzip,deflate,br']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if ($header) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if ($referer) {
            if ($referer == 1) {
                curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');
            } else {
                curl_setopt($ch, CURLOPT_REFERER, $referer);
            }
        }
        if ($ua) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1');
        }
        if ($nobaody) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

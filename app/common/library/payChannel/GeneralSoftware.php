<?php

// 还原自 Swoole Compiler 加密文件: GeneralSoftware.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/GeneralSoftware.php

namespace app\common\library\payChannel;

class GeneralSoftware implements \app\common\service\channel\ChannelInterface {
    protected \app\admin\model\channel\Account $account;
    public function __construct(\app\admin\model\channel\Account $account)
    {
        // [2026-10-02 opcode 对照修复] 行 25: 仅装配 account（GeneralSoftware 无 config 属性）
        $this->account = $account;
    }

    public function pay(\app\admin\model\Order|null $order = null): \app\common\service\channel\PayInfo
    {
        $extend = $order->extend;
        $extend['h5_auto_jump'] = 'not';
        $order->extend = $extend;
        $order->save();
        $config = $this->account->config;
        $payInfo = new \app\common\service\channel\PayInfo();
        if (!empty($config['qrcode'])) {
            $payInfo->setQrcode($config['qrcode']);
        } else {
            if (empty($config['image_url'])) {
                throw new Exception('二维码图片不存在');
            }
            $payInfo->setQrUrl(request()->domain() . $config['image_url']);
        }
        $payInfo->setH5Url($this->account->pay_type == 'wxpay' ? 'weixin://' : 'alipays://platformapi/startapp?saId=10000007');
        return $payInfo;
    }

    public function callback(\app\admin\model\Order|null $order = null): bool
    {
    return true;
    }

    // [opcode 对照] $scene 默认值按 RECV_INIT op2=LIT('add') 真值补回（原签名 null 为管线丢失）
    public function handleparams(array|null $params = null, string|null $scene = 'add'): void
    {
        $config = $this->account->config;
        if (empty($config['image_url'])) {
            throw new Exception('请上传图片');
        }
        if ($this->account->pay_type == 'wxpay') {
            if (empty($config['type'])) {
                throw new Exception('请选择二维码类型');
            }
        }
        if (isset($config['auto_decode']) && in_array($config['auto_decode'], ['1', 1])) {
            if (!isset($config['type']) || $config['type'] != 'zs') {
                $attachment = \app\common\model\Attachment::where('url', $config['image_url'])->find();
                if ($attachment) {
                    $diskConfig = config('filesystem.disks.' . $attachment->disk ?? 'public');
                    $filePath = $diskConfig['root'] . $attachment->url;
                    $qrUtil = new \app\common\library\QrCodeUtil();
                    try {
                        $decode = $qrUtil->decodeFromFile($filePath);
                    } catch (Exception $e) {
                        throw new Exception('图片解析失败:' . $e->getMessage());
                    }
                    if ($decode['format'] == 'URL') {
                        $config['qrcode'] = $decode['content'];
                        $this->account->config = $config;
                    }
                } else {
                    $config['qrcode'] = $config['image_url'];
                    $this->account->config = $config;
                }
            }
        }
        $this->account->save();
    }

    public function handledelete(): void
    {
    return;
    }

}

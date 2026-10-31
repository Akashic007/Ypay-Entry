<?php

// 还原自 Swoole Compiler 加密文件: ChannelFactory.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/channel/ChannelFactory.php

namespace app\common\service\channel;

class ChannelFactory{
    // [2026-10-02 opcode 真值修复] 原 `protected static array $map;` 为无默认值的 typed property
    // （访问即 Error）。默认值从加密件 class_entry+48 的 default_static_members_table 直读：
    protected static array $map = [
        'alipay_personal_not_hang' => 'app\\common\\library\\payChannel\\AlipayPersonalNotHang',
        'wxpay_official_special'   => [
            'local' => 'app\\common\\library\\payChannel\\WxpayOfficialSpecial',
            'up'    => 'app\\common\\library\\payChannel\\up\\WxpayOfficialSpecial',
            'down'  => 'app\\common\\library\\payChannel\\WxpayOfficialSpecial',
        ],
        'wxpay_software'  => 'app\\common\\library\\payChannel\\GeneralSoftware',
        'alipay_software' => 'app\\common\\library\\payChannel\\GeneralSoftware',
        'alipay_sign'     => [
            'local' => [
                'JSAPI' => 'app\\common\\library\\payChannel\\alipaySign\\Jsapi',
                'FACE'  => 'app\\common\\library\\payChannel\\alipaySign\\FaceToFace',
            ],
            'down'  => [
                'JSAPI' => 'app\\common\\library\\payChannel\\alipaySign\\Jsapi',
                'FACE'  => 'app\\common\\library\\payChannel\\alipaySign\\FaceToFace',
            ],
            'up'    => 'app\\common\\library\\payChannel\\up\\AlipaySign',
        ],
        'alipay_scan' => 'app\\common\\library\\payChannel\\alipay\\AlipayScan',
        'wxpay_scan'  => [
            'v2' => 'app\\common\\library\\payChannel\\wxpay\\scan\\V2',
        ],
    ];
    public static function create(\app\admin\model\channel\Account|null $account = null): \app\common\service\channel\ChannelInterface
    {
        switch ($account->channel_type) {
            case 'alipay_personal_not_hang':
            case 'wxpay_software':
            case 'alipay_software':
            case 'alipay_scan':
                $class = self::$map[$account->channel_type] ?? null;
                break;
            case 'wxpay_official_special':
                $class = self::$map[$account->channel_type][$account->source_type] ?? null;
                break;
            case 'alipay_sign':
                if (empty($account->config['api_type'])) {
                    throw new \ba\Exception('通道接口类型为空');
                }
                $class = self::$map[$account->channel_type][$account->config['api_type']] ?? null;
                break;
            case 'wxpay_scan':
                if (isset($account->config['api_version']) && $account->config['api_version'] == 'v3') {
                    $class = self::$map[$account->channel_type]['v3'] ?? null;
                } else {
                    $class = self::$map[$account->channel_type]['v2'] ?? null;
                }
                break;
            default:
                throw new \ba\Exception('不支持的通道类型：' . $account->channel_type);
        }
        if (!$class || !class_exists($class)) {
            throw new \ba\Exception('通道类不存在');
        }
        return new $class($account);
    }

}

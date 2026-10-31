<?php

// 还原自 Swoole Compiler 加密文件: AlipaySign.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/entryChannel/up/AlipaySign.php

namespace app\common\library\entryChannel\up;

class AlipaySign extends \app\common\library\entryChannel\up\UpEntry implements \app\common\service\entry\contract\AlipayOfficial {
    public function apply(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null, bool|null $draft = null): void
    {
    $applyData = $this->mediaBuild($mchEntry->apply_data);
    $mchEntry->apply_data = $applyData;
    parent::apply($mchEntry, $draft);
    }

    private function mediabuild(array|null $data = null): array
    {
    $attachments = $this->collectAttachments($data);
    if (empty($attachments)) {
        return $data;
    }
    // [opcode 对照] 15 个字段按 arData 原始序直读（dump 显示层被截断为 8 个）
    foreach (['business_license_pic', 'special_license_pic', 'shop_scene_pic', 'shop_sign_board_pic', 'app_home_screenshot', 'app_pay_screenshot', 'app_item_screenshot', 'h_5_home_screenshot', 'h_5_item_screenshot', 'h_5_pay_screenshot', 'h_5_sites_loa', 'web_home_screenshot', 'web_item_screenshot', 'web_pay_screenshot', 'web_sites_loa'] as $field) {
        if (empty($data[$field]) || !is_string($data[$field])) {
            continue;
        }
        $data[$field] = $this->resolveUpUrl($attachments, $data[$field]);
    }
    return $data;
    }

    private function collectattachments(array|null $data = null): array
    {
    $urls = [];
    array_walk_recursive($data, function ($value) use (&$urls) {
        if (is_string($value) && $value !== '') {
            $urls[] = $value;
        }
    });
    return $urls ? \app\common\model\Attachment::whereIn('url', $urls)->column('extend', 'url') : [];
    }

    private function resolveupurl(array|null $attachments = null, null|string $url = null): null|string
    {
    if (empty($url) || !isset($attachments[$url])) {
        return $url;
    }
    $up = $attachments[$url];
    if (is_string($up)) {
        $up = json_decode($up, true);
    }
    return $up['up_url'] ?? $url;
    }

    public function getconfig(): array
    {
    $config = [];
    if (empty($this->channel->config['payCapabilities'])) {
        return $config;
    }
    $config['payCapabilities'] = $this->channel->config['payCapabilities'];
    return $config;
    }

}

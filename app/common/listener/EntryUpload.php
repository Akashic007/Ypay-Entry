<?php

// 还原自 Swoole Compiler 加密文件: EntryUpload.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/listener/EntryUpload.php

namespace app\common\listener;

class EntryUpload{
    public function handle(\app\common\model\Attachment|null $attachment = null): void
    {
        $isOcr = request()->param('is_ocr');
        $ocrType = request()->param('ocr_type');
        $params = request()->param(['ec_id']);
        if (empty($params['ec_id'])) {
            throw new \Exception('进件通道ID为空');
        }
        try {
            $entryRequest = new \app\common\service\entry\EntryRequest();
            $channel = \app\admin\model\EntryChannel::find($params['ec_id']);
            if (empty($channel)) {
                throw new \ba\Exception('进件通道不存在');
            }
            $entryRequest->setChannel($channel);
            $service = new \app\common\service\EntryService($entryRequest);
            $service->uploadMedia($attachment);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        if (isset($isOcr) && $isOcr == 'true' && get_sys_config('ocr_config') != 'close') {
            try {
                \app\common\service\OcrService::recognize($attachment, $ocrType);
            } catch (\Exception $e) {
                \think\facade\Log::error('图片识别出现异常：' . $e->getMessage());
            }
        }
    }

}

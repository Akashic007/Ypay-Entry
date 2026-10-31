<?php

// 还原自 Swoole Compiler 加密文件: OcrService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/OcrService.php

namespace app\common\service;

class OcrService{
    public static function recognize(\app\common\model\Attachment|null $attachment = null, string|null $type = null): mixed
    {
        // [opcode 对照] RECV_INIT 真值: $type 默认 'General'(lit0); $attachment 为必填(RECV)
        $config = get_sys_config('ocr_config');                                                                                     // line 26
        $path = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $attachment->url;                  // line 27
        switch ($config['type']) {                                                                                                  // line 29
            case 'aliyun_ocr':                                                                                                      // line 30
                if (!isset($config['accessKeyId']) || !isset($config['accessKeySecret'])) {                                         // line 31
                    throw new \Exception('阿里云OCR未正确配置');                    // line 49(编译器后置)
                }
                $ocr = new \app\common\library\aliyun\UnifyOcr($config['accessKeyId'], $config['accessKeySecret']);             // line 32
                try {
                    $result = $ocr->recognize($path, $type);                                                                        // line 34
                    $extend = $attachment->extend;                                                                                  // line 36
                    $extend['ocr'] = $result;                                                                                       // line 37
                    $attachment->extend = $extend;                                                                                  // line 38
                    $attachment->save();                                                                                            // line 39
                } catch (\Throwable $e) {                                                                                          // line 40
                    $attachment->extend = ['ocr' => '图片识别异常：' . $e->getMessage()];   // line 41-42
                    $attachment->save();                                                                                            // line 44
                    throw new \Exception('图片识别异常：' . $e->getMessage());             // line 45
                }
                return $result;                                                                                                     // line 47
            default:
                throw new \Exception('暂不支持的识别服务');                        // line 51
        }
    }

}

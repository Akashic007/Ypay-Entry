<?php

// 还原自 Swoole Compiler 加密文件: QrCodeUtil.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/QrCodeUtil.php

namespace app\common\library;

class QrCodeUtil{
    public function decodeFromFile(string|null $filePath = null): array
    {
        try {
            if (!file_exists($filePath)) {
                throw new \Exception('二维码文件不存在');
            }
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'png', 'jpeg', 'gif'])) {
                throw new \Exception('不支持的文件格式，仅支持 JPEG、PNG、GIF');
            }
            $qrReader = new \Zxing\QrReader($filePath);
            $content = $qrReader->text();
            if (empty($content)) {
                throw new \Exception('未检测到二维码内容');
            }
            return [
                'success' => true,
                'content' => $content,
                'format' => $this->detectFormat($content),
                'type' => $this->detectContentType($content),
            ];
        } catch (\Exception $e) {
            throw new \Exception('二维码解析失败: ' . $e->getMessage());
        }
    }

    public function decodeFromBase64(string|null $base64Data = null): array
    {
        try {
            if (str_starts_with($base64Data, 'data:image')) {
                $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
            }
            $imageData = base64_decode($base64Data);
            if (!$imageData) {
                throw new \Exception('Base64数据解码失败');
            }
            $tempFile = tempnam(sys_get_temp_dir(), 'qrcode_');
            file_put_contents($tempFile, $imageData);
            $result = $this->decodeFromFile($tempFile);
            unlink($tempFile);
            return $result;
        } catch (\Exception $e) {
            unlink($tempFile);
            throw $e;
        }
    }

    private function detectFormat(string|null $content = null): string
    {
        if (filter_var($content, FILTER_VALIDATE_URL)) {
            return 'URL';
        }
        if (str_starts_with($content, 'BEGIN:VCARD')) {
            return 'VCARD';
        }
        if (preg_match('/^WIFI:/i', $content)) {
            return 'WIFI';
        }
        if (json_decode($content) !== null) {
            return 'JSON';
        }
        return 'TEXT';
    }

    private function detectContentType(string|null $content = null): string
    {
        if (filter_var($content, FILTER_VALIDATE_URL)) {
            return 'link';
        }
        if (preg_match('/^[0-9]{11,}$/', $content)) {
            return 'phone';
        }
        if (filter_var($content, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        if (preg_match('/^[A-Za-z0-9]{6,}$/', $content)) {
            return 'code';
        }
        return 'text';
    }

}

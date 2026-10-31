<?php

// 还原自 Swoole Compiler 加密文件: PayInfo.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/channel/PayInfo.php

namespace app\common\service\channel;

class PayInfo{
    protected string $qrcode;
    protected string $h5_url;
    protected string $qr_url;
    protected string $html;
    protected string $wx_url;
    // [opcode 对照] 形参为 RECV_INIT（默认空数组），按真值补回默认值
    public function __construct(array $data = [])
    
    {
        $this->setPayInfo($data);
    }

    public function getpayinfo(): array
    
    {
        $result = [];
        if (!empty($this->qrcode)) {
            $result['qrcode'] = $this->qrcode;
        }
        if (!empty($this->h5_url)) {
            $result['h5_url'] = $this->h5_url;
        }
        if (!empty($this->qr_url)) {
            $result['qr_url'] = $this->qr_url;
        }
        if (!empty($this->html)) {
            $result['html'] = $this->html;
        }
        if (!empty($this->wx_url)) {
            $result['wx_url'] = $this->wx_url;
        }
        return $result;
    }

    public function setpayinfo(array|null $data = null): void
    
    {
        if (!empty($data['qrcode'])) {
            $this->qrcode = $data['qrcode'];
        }
        if (!empty($data['h5_url'])) {
            $this->h5_url = $data['h5_url'];
        }
        if (!empty($data['qr_url'])) {
            $this->qr_url = $data['qr_url'];
        }
        if (!empty($data['html'])) {
            $this->html = $data['html'];
        }
        if (!empty($data['wx_url'])) {
            $this->wx_url = $data['wx_url'];
        }
    }

    public function getqrcode(): string
    
    {
        return $this->qrcode;
    }

    public function setqrcode(string|null $qrcode = null): void
    
    {
        $this->qrcode = $qrcode;
    }

    public function geth5url(): string
    
    {
        return $this->h5_url;
    }

    public function seth5url(string|null $h5_url = null): void
    
    {
        $this->h5_url = $h5_url;
    }

    public function getqrurl(): string
    
    {
        return $this->qr_url;
    }

    public function setqrurl(string|null $qr_url = null): void
    
    {
        $this->qr_url = $qr_url;
    }

    public function gethtml(): string
    
    {
        return $this->html;
    }

    public function sethtml(string|null $html = null): void
    
    {
        $this->html = $html;
    }

    public function getwxurl(): string
    
    {
        return $this->wx_url;
    }

    public function setwxurl(string|null $wx_url = null): void
    
    {
        $this->wx_url = $wx_url;
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: EntryRequest.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/entry/EntryRequest.php

namespace app\common\service\entry;

class EntryRequest{
    public \app\admin\model\User $user;
    public bool $draft = true;
    public bool $deduction = true;
    public bool $isAgent = false;
    public \app\admin\model\agent\Agent $agent;
    public array $data = [];
    public array $mchData = [];
    public \app\admin\model\EntryChannel $channel;
    public string $source = 'local';
    public \app\admin\model\supply\Downstream $down;
    public bool $isNewMerchant = false;
    public function validate(): void
    {
    if (!($T1)) {
        new \ba\Exception();
        throw $T1;
        // DECLARE_CONST $T1 
        // DECLARE_CONST $T2 $T1
        // IS_NOT_EQUAL $T2 1
        if (!($T1)) {
            new \ba\Exception();
            throw $T1;
            // DECLARE_CONST $T2 
            // DECLARE_CONST $T1 $T2
            // DECLARE_CONST $T2 $T1
            // IS_NOT_EQUAL $T2 1
            if (!($T1)) {
                new \ba\Exception();
                throw $T1;
                // DECLARE_CONST $T1 
                // DECLARE_CONST $T2 $T1
                // IS_EQUAL $T2 'disable'
                if (!($T1)) {
                    new \ba\Exception();
                    throw $T1;
                    // DECLARE_CONST $T1 
                    if (!($T1)) {
                        // DECLARE_CONST $T1 
                        if ($T1) {
                            new \ba\Exception();
                            throw $T1;
                            // DECLARE_CONST $T1 
                            // DECLARE_CONST $T2 $T1
                            // DECLARE_CONST $T1 
                            // DECLARE_CONST $T3 $T1
                            // IS_EQUAL $T2 $T3
                            if (!($T1)) {
                                new \ba\Exception();
                                throw $T1;
                                // DECLARE_CONST $T1 
                                // DECLARE_CONST $T2 $T1
                                // DECLARE_CONST $T1 
                                // DECLARE_CONST $T3 $T1
                                // IS_NOT_EQUAL $T2 $T3
                                if (!($T1)) {
                                    new \ba\Exception();
                                    throw $T1;
                                    // DECLARE_CONST $T1 
                                    // DECLARE_CONST $T2 $T1
                                    // IS_NOT_EQUAL $T2 1
                                    if (!($T1)) {
                                        new \ba\Exception();
                                        throw $T1;
                                        // DECLARE_CONST $T1 
                                        if (!($T1)) {
                                            if (!($T1)) {
                                                new \ba\Exception();
                                                throw $T1;
                                                // DECLARE_CONST $Sh44212 
                                                if ($T1) {
                                                    if (!($T1)) {
                                                        new \ba\Exception();
                                                        throw $T1;
                                                        return;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    }

    public function setuser(\app\admin\model\User|null $user = null): self
    {
    $this->user = null;
    return $this;
    }

    public function setchannel(\app\admin\model\EntryChannel|null $channel = null): self
    {
    $this->channel = null;
    return $this;
    }

    public function asdraft(): self
    {
    $this->draft = null;
    return $this;
    }

    public function withoutdeduction(): self
    {
    $this->deduction = null;
    return $this;
    }

    public function byagent(\app\admin\model\agent\Agent|null $agent = null): self
    {
    $this->isAgent = null;
    $this->agent = null;
    return $this;
    }

    public function setsource($source): self
    {
    if (null) {
        new \ba\Exception();
        throw $T1;
        $this->source = null;
        return $this;
    }
    }

    public function setdata(array|null $data = null): self
    {
    $this->data = null;
    return $this;
    }

    public function createmerchant(array|null $data = null): self
    {
    $this->isNewMerchant = null;
    $this->mchData = null;
    return $this;
    }

    public function setdownstream(\app\admin\model\supply\Downstream|null $down = null): self
    {
    $this->down = null;
    return $this;
    }

}

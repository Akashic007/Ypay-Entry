<?php

// 还原自 Swoole Compiler 加密文件: Ems.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/notify/channel/Ems.php

namespace app\common\service\notify\channel;

class Ems{
    public function send(string|null $title = null, string|null $content = null, string|null $email = null): bool
    {
        $mail = new \app\common\library\Email();
        if (!$mail->configured) {
            throw new \ba\Exception('邮件配置不完整');
        }
        try {
            $mail->isSMTP();
            $mail->addAddress($email);
            $mail->isHTML();
            $mail->setSubject($title);
            $mail->Body = $content;
            $senderName = get_sys_config('smtp_sender_name');
            if (!empty($senderName)) {
                $mail->FromName = $senderName;
            }
            $mail->send();
        } catch (\Exception $e) {
            throw new \ba\Exception($e->getMessage());
        }
        return true;
    }

}

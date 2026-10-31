<?php

namespace modules\mail\library;

use Throwable;
use app\common\library\Email;
use app\admin\model\mail\Template;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mail
{
    /**
     * @param int|string $name  模板ID或name
     * @param string     $email 收件邮件
     * @param array      $vars  模板变量
     * @return array    ['code'=>1, 'msg'=>'ok]
     * @throws Throwable
     */
    public static function send(int|string $name, string $email, array $vars = []): array
    {
        $mail = new Email();
        if (!$mail->configured) {
            return [
                'code' => 0,
                'msg'  => '系统配置->邮件配置不完整'
            ];
        }

        $data = Template::where('id|name', $name)
            ->field('id,title,content,status')
            ->find();
        if (!$data) {
            return [
                'code' => 0,
                'msg'  => '模板不存在'
            ];
        }
        if ($data['status'] == 0) {
            return [
                'code' => 0,
                'msg'  => '模板已禁用'
            ];
        }

        $body  = $data->content;
        $array = [];
        if (count($vars) > 0) {
            foreach ($vars as $key => $value) {
                $newKey         = '{' . $key . '}';
                $array[$newKey] = $value;
            }
            $body = str_replace(array_keys($array), array_values($array), $body);
        }
        try {
            $mail->isSMTP();
            $mail->addAddress($email);
            $mail->isHTML();
            $mail->setSubject($data->title);
            $mail->Body = $body;
            $mail->send();
        } catch (PHPMailerException) {
            return [
                'code' => 0,
                'msg'  => $mail->ErrorInfo
            ];
        }
        return [
            'code' => 1,
            'msg'  => 'ok'
        ];
    }
}
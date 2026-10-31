<?php

use app\admin\model\channel\Type;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV088 extends Migrator
{

    public function change()
    {
        $this->channelType();
        $this->attachment();
        $this->config();
        Db::name('config')->where('name', 'version')->update(['value' => 'V0.8.8']);
    }

    private function channelType(): void
    {
        $wxpay_exist = Type::where('type', 'wxpay_software')->find();
        if (empty($wxpay_exist)) {
            Type::create([
                'pay_type' => 'wxpay',
                'type' => 'wxpay_software',
                'name' => '微信软件版',
                'status' => 1
            ]);
        }

        $alipay_exist = Type::where('type', 'alipay_software')->find();
        if (empty($alipay_exist)) {
            Type::create([
                'pay_type' => 'alipay',
                'type' => 'alipay_software',
                'name' => '支付宝软件版',
                'status' => 1
            ]);
        }
    }

    private function attachment(): void
    {
        // 新增磁盘字段
        $table = $this->table('attachment');
        if (!$table->hasColumn('disk')) {
            $table->addColumn('disk', 'string', [
                'comment' => '存储磁盘',
                'default' => '',
                'null' => true,
                'limit' => 50,
                'after' => 'url'
            ])->update();
        }
    }

    private function config(): void
    {
        // 新增找回密码
        $exist_find_pwd = Db::name('config')->where('name', 'recover_method')->find();
        if (empty($exist_find_pwd)) {
            Db::name('config')->insert([
                'name' => 'recover_method',
                'group' => 'login_reg',
                'title' => '找回方式',
                'tip' => '',
                'type' => 'selects',
                'value' => '["email","mobile"]',
                'content' => '{"mobile":"\u624b\u673a\u53f7","email":"\u90ae\u7bb1"}',
                'rule' => '',
                'allow_del' => 0,
                'extend' => '',
                'weigh' => 8,
            ]);
        }

        // 新增用户名规则
        $exist_username_rule = Db::name('config')->where('name', 'username_rule')->find();
        if (empty($exist_username_rule)) {
            Db::name('config')->insert([
                'name' => 'username_rule',
                'group' => 'login_reg',
                'title' => '用户名规则',
                'tip' => '',
                'type' => 'checkbox',
                'value' => '["number","alphabet"]',
                'content' => '{"number":"\u6570\u5b57","alphabet":"\u5b57\u6bcd","symbol":"\u7b26\u53f7","unlimited":"\u4e0d\u9650\u5236"}',
                'rule' => '',
                'allow_del' => 0,
                'extend' => '',
                'weigh' => 7,
            ]);
        }

        // 新增用户名规则
        $exist_password_rule = Db::name('config')->where('name', 'password_rule')->find();
        if (empty($exist_password_rule)) {
            Db::name('config')->insert([
                'name' => 'password_rule',
                'group' => 'login_reg',
                'title' => '密码规则',
                'tip' => '',
                'type' => 'checkbox',
                'value' => '["number","alphabet","symbol"]',
                'content' => '{"number":"\u6570\u5b57","alphabet":"\u5b57\u6bcd","symbol":"\u7b26\u53f7","unlimited":"\u4e0d\u9650\u5236"}',
                'rule' => '',
                'allow_del' => 0,
                'extend' => '',
                'weigh' => 6,
            ]);
        }

        // 排序一些配置
        Db::name('config')->where('name', 'login_method')->update(['weigh' => 10]);
        Db::name('config')->where('name', 'reg_method')->update(['weigh' => 9]);
    }
}

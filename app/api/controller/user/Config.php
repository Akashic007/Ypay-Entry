<?php

// 还原自 Swoole Compiler 加密文件: Config.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/user/Config.php

namespace app\api\controller\user;

class Config extends \app\common\controller\Frontend {
    protected \app\admin\model\user\Config $model;
    public function initialize(): void
    {
        parent::initialize();
        $this->model = (new \app\admin\model\user\Config())->userInitConfig($this->auth->id);
    }

    public function paytemplate(): void
    {
        $root = root_path('public') . 'views' . DIRECTORY_SEPARATOR . 'pay-template' . DIRECTORY_SEPARATOR;
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!is_dir($root)) {
            $this->error('模板目录不存在');
        }

        if (request()->isPost()) {
            $name = request()->param('name');
            if (empty($name)) {
                $this->error('模板名称为空');
            }
            if (!is_dir($root . $name)) {
                $this->error('模板目录不存在');
            }
            $config = $this->model->basic;
            $config['pay_template'] = $name;
            $this->model->basic = $config;
            $this->model->save();
            $this->success('操作成功');
        }

        $templates = [];
        $dirs = scandir($root);
        foreach ($dirs as $dirName) {
            if ($dirName == '.' || $dirName == '..') {
                continue;
            }
            $templatePath = $root . $dirName . DIRECTORY_SEPARATOR;
            if (!is_dir($templatePath)) {
                continue;
            }
            try {
                $configFile = $templatePath . 'config.php';
                if (!is_file($configFile)) {
                    continue;
                }
                $config = include $configFile;
                if (!is_array($config) || empty($config['title'])) {
                    continue;
                }
                $config['name'] = $dirName;
                $image = '';
                foreach ($imageExtensions as $extension) {
                    if (is_file($templatePath . 'image.' . $extension)) {
                        $image = '/views/pay-template/' . $dirName . '/image.' . $extension;
                    }
                }
                $config['image'] = $image;
                $templates[] = $config;
            } catch (Exception $e) {
                continue;
            }
        }

        $this->success('ok', [
            'list'             => $templates,
            'total'            => count($templates),
            'current_template' => $this->model->basic['pay_template'] ?? 'default',
        ]);
    }

    public function channelconfig(): void
    {
        $row = \app\admin\model\user\Config::where('user_id', $this->auth->id)->find();
        $config = $row->channel_config ?? [];

        if (request()->isGet()) {
            $this->success('ok', $config);
        }

        $post = request()->post(['floating_amount', 'timeout_url', 'timeout_jump_type', 'timeout', 'alipay_jump_type', 'order_prefix', 'channel_call_mode', 'is_site_name_params']);
        $post = array_filter($post, function ($value) {
            return $value !== null && $value !== '';
        });
        $row->channel_config = array_merge($config, $post);
        $row->save();

        $this->success('操作成功', array_merge($config, $post));
    }

    public function notifyconfig(): void
    {
        $row = \app\admin\model\user\Config::where('user_id', $this->auth->id)->find();
        $config = $row->notify_config ?? [];

        if (request()->isGet()) {
            $this->success('ok', $config);
        }

        $post = request()->post(['notify_method', 'user_login_notify', 'user_reset_password_notify', 'user_balance_insufficient_notify', 'order_create_notify', 'order_pay_success_notify', 'order_callback_notify', 'create_channel_notify', 'update_channel_notify', 'channel_offline_notify']);
        $post = array_filter($post, function ($value) {
            return $value !== null && $value !== '';
        });
        $row->notify_config = array_merge($config, $post);
        $row->save();

        $this->success('操作成功', array_merge($config, $post));
    }
}

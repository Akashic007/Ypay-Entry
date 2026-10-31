<?php

// 还原自 Swoole Compiler 加密文件: Config.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/sms/Config.php

namespace app\admin\controller\sms;

class Config extends \app\common\controller\Backend {
    public static string $smsConfigFile;
    public function getconfigkey(): void
    {
        $name = $this->request->get('name', '');
        if (!$name) {
            $this->error(__('Parameter error'));
        }
        if ($name == 'base') {
            $config = [
                'timeout'  => \think\facade\Config::get('sms.timeout'),
                'strategy' => \think\facade\Config::get('sms.default.strategy'),
                'gateways' => \think\facade\Config::get('sms.default.gateways'),
            ];
        } else {
            $config = \think\facade\Config::get('sms.gateways.' . $name);
        }
        $this->success('', ['config' => $config]);
    }

    public function saveconfig(): void
    {
        $type = $this->request->get('type', '');
        $data = $this->request->post();
        if (!$type) {
            $this->error(__('Parameter error'));
        }
        $config     = \think\facade\Config::get('sms');
        $configFile = config_path() . self::$smsConfigFile;
        $content    = @file_get_contents($configFile);
        if ($type == 'base') {
            $timeout  = (int)($data['timeout']);
            $strategy = $data['strategy'] == 'order' ? 'Overtrue\\EasySms\\Strategies\\OrderStrategy' : 'Overtrue\\EasySms\\Strategies\\RandomStrategy';
            $config['default']['strategy'] = str_replace('\\', '\\\\', $config['default']['strategy']);
            $str = '[';
            if ($data['gateways']) {
                foreach ($data['gateways'] as $gateway) {
                    $str .= ("'" . $gateway . "', ");
                }
            }
            $str  = rtrim($str, ', ');
            $str .= ']';
            $content = preg_replace("/'timeout'(\s+)=>(\s+)'" . $config['timeout'] . "'/", "'timeout'\$1=>\$2'" . $timeout . "'", $content);
            $content = preg_replace("/'strategy'(\s+)=>(\s+)'" . $config['default']['strategy'] . "'/", "'strategy'\$1=>\$2'" . $strategy . "'", $content);
            $content = preg_replace("/'gateways'(\s+)=>(\s+)\[.*?]/", "'gateways'\$1=>\$2" . $str, $content);
        } elseif ($type == 'gateway') {
            $name = $this->request->get('name', '');
            if (!isset($config['gateways'][$name]) || !is_array($config['gateways'][$name])) {
                $this->error(__('Parameter error'));
            }
            foreach ($config['gateways'][$name] as $key => $value) {
                $content = preg_replace("/'" . $key . "'(\s+)=>(\s+)'" . $value . "',#" . $name . "#/", "'" . $key . "'\$1=>\$2'" . $data[$key] . "',#" . $name . "#", $content);
            }
        }
        $result = @file_put_contents($configFile, $content);
        if ($result) {
            $this->success();
        } else {
            $this->error(__('Configuration write failed: %s', ['config/' . self::$smsConfigFile]));
        }
    }}

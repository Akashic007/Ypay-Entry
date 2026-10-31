<?php

// 还原自 Swoole Compiler 加密文件: HomeTemplate.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/theme/HomeTemplate.php

namespace app\admin\controller\theme;

class HomeTemplate extends \app\common\controller\Backend {
    private array $imageExtensions = ['jpg', 'jpeg', 'png'];
    private string $templateRoot;
    public function initialize(): void
    {
        parent::initialize();
        $this->templateRoot = root_path('public') . 'views' . DIRECTORY_SEPARATOR . 'home-template' . DIRECTORY_SEPARATOR;
    }

    public function gettemplatelist(): void
    {
        if (!is_dir($this->templateRoot)) {
            $this->error('模板目录不存在');
        }
        $templates = [];
        $dirs = scandir($this->templateRoot);
        foreach ($dirs as $dirName) {
            if ($dirName === '.' || $dirName === '..') {
                continue;
            }
            $templatePath = $this->templateRoot . $dirName . DIRECTORY_SEPARATOR;
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
                $config['image'] = $this->findtemplateimage($templatePath, $dirName);
                $templates[] = $config;
            } catch (\Exception) {
                continue;
            }
        }
        $this->success('ok', [
            'list'             => $templates,
            'total'            => count($templates),
            'current_template' => get_sys_config('home_template'),
        ]);
    }

    public function enabletemplate(): void
    {
        $name = $this->request->param('name');
        if (empty($name)) {
            $this->error('模板名称为空');
        }
        if (!is_dir($this->templateRoot . $name)) {
            $this->error('模板目录不存在');
        }
        \app\admin\model\Config::where('name', 'home_template')->update(['value' => $name]);
        \think\facade\Cache::delete('home_template');
        $this->success('操作成功');
    }

    private function findtemplateimage(string|null $templatePath = null, string|null $dirName = null): null|string
    {
        foreach ($this->imageExtensions as $ext) {
            if (is_file($templatePath . 'image.' . $ext)) {
                return '/views/home-template/' . $dirName . '/image.' . $ext;
            }
        }
        return null;
    }

}

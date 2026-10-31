<?php

// 还原自 Swoole Compiler 加密文件: Ajax.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Ajax.php

namespace app\api\controller;

class Ajax extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['area', 'preview', 'buildSuffixSvg'];
    protected array $noNeedPermission = ['upload'];
    public function initialize(): void
    {
        parent::initialize();
    }

    public function upload(): void
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error('上传文件不能为空');
        }
        $driver  = $this->request->param('driver', 'local') ?? 'local';
        $topic   = $this->request->param('topic', 'default') ?? 'default';
        $disks   = $this->request->param('disks', 'public') ?? 'public';
        $purpose = $this->request->param('purpose', 'default') ?? 'default';
        \think\facade\Config::set(\think\facade\Config::get('filesystem.disks.' . $disks), 'upload_disks');
        if ($purpose == 'entry') {
            $size    = $file->getSize();
            $maxSize = 2097152;
            if ($size > $maxSize) {
                $this->error('进件资料文件不能超过2M');
            }
        }
        try {
            $upload     = new \app\common\library\Upload();
            $attachment = $upload
                ->setFile($file)
                ->setDriver($driver)
                ->setTopic($topic)
                ->upload(null, 0, $this->auth->id);
            $attachment->disk = $disks ?? 'public';
            $attachment->save();
            if ($purpose == 'entry') {
                event('EntryUpload', $attachment);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('File uploaded successfully'), [
            'file' => $attachment->toArray() ?? []
        ]);
    }

    public function area(): void
    {
        $this->success('', get_area());
    }

    public function buildsuffixsvg(): \think\Response
    {
        $suffix     = $this->request->param('suffix', 'file');
        $background = $this->request->param('background');
        $content    = build_suffix_svg((string)$suffix, (string)$background);
        return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/svg+xml');
    }

    public function preview(): \think\response\File
    {
        $url = $this->request->param('url');
        if (empty($url)) {
            $this->error('资源URL为空');
        }
        $token = $this->request->param('token');
        if (empty($token)) {
            $this->error('TOKEN为空');
        }
        try {
            $user = \app\common\facade\Token::get($token);
            if (empty($user)) {
                $this->error('TOKEN信息为空');
            }
            \app\common\facade\Token::tokenExpirationCheck($user);
        } catch (\Throwable $e) {
            $this->error('登录已过期');
        }
        $attachment = \app\common\model\Attachment::where('url', $url)->find();
        if (empty($attachment)) {
            $this->error('文件不存在');
        }
        $path = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $url;
        if (!is_file($path)) {
            $this->error('文件不存在');
        }
        return download($path)->force(false);
    }

}

<?php

use think\facade\Db;
use think\migration\Migrator;

class UpdateV086 extends Migrator
{
    public function change()
    {
        $this->attr();

        Db::name('config')->where('name', 'version')->update(['value' => 'V0.8.6']);
    }

    // 更新附件表
    private function attr(): void
    {
        Db::startTrans();
        try {
            $attrs = Db::name('attachment')->whereNotNull('media_id')->select();
            if ($attrs->count() > 0) {
                foreach ($attrs as $item) {
                    $media = [];
                    if (str_starts_with($item['media_id'], 'V1')) {
                        $media['wxpay'] = $item['media_id'];
                    }
                    $media_id = json_decode($item['media_id'], true);
                    if (is_array($media_id) && isset($media_id['wxpay'])) {
                        $media['wxpay'] = $media_id['wxpay'];
                    }
                    if (is_array($media_id) && isset($media_id['alipay'])) {
                        $media['alipay'] = $media_id['alipay'];
                    }
                    Db::name('attachment')->save([
                        'id' => $item['id'],
                        'media_id' => json_encode($media)
                    ]);
                }
            }
            Db::commit();
        } catch (\Exception) {
            Db::rollback();
        }
    }
}

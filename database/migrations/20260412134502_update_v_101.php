<?php

use think\migration\Migrator;
use think\facade\Db;


class UpdateV101 extends Migrator
{
    public function change(): void
    {
        $this->updateAgentTable();
        $this->upMerchantTable();
        $this->upMerchantEntryTable();
        Db::name('config')->where('name', 'version')->update(['value' => 'V1.0.1']);
    }


    public function updateAgentTable(): void
    {
        $tab = $this->table('agent');
        // 删除代理表中的可提现余额字段
        if ($tab->hasColumn('withdrawal_money')) {
            $tab->removeColumn('withdrawal_money');
        }

        // 代理表删除pay_account字段
        if ($tab->hasColumn('pay_account')) {
            $tab->removeColumn('pay_account');
        }
        $tab->update();
    }


    public function upMerchantTable(): void
    {
        $tab = $this->table('merchant');
        // 商户表删除entry_id字段
        if ($tab->hasColumn('entry_id')) {
            $tab->removeColumn('entry_id');
        }
        $tab->update();

    }

    public function upMerchantEntryTable(): void
    {
        $tab = $this->table('merchant_entry');
        // 商户进件记录表的supply字段和up_id字段可以删除
        if ($tab->hasColumn('supply')) {
            $tab->removeColumn('supply');
        }
        if ($tab->hasColumn('up_id')) {
            $tab->removeColumn('up_id');
        }
        $tab->update();
    }
}

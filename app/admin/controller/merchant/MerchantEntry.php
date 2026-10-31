<?php

// 还原自 Swoole Compiler 加密文件: MerchantEntry.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/merchant/MerchantEntry.php

namespace app\admin\controller\merchant;

class MerchantEntry extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', null, 'create_time'];
    protected array $withJoinTable = ['user', 'sp'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        // [opcode 对照] initialize 字面量 + line=35 处 GET_CALLED_CLASS/DO_FCALL 调用先于 line=36 的 NEW，即 parent::initialize()
        parent::initialize();
        $this->model = new \app\admin\model\merchant\MerchantEntry();
    }

    public function add(): void
    {
        $data = $this->request->post();
        if (empty($data['user_id'])) {
            $this->error('用户ID为空');
        }
        if (empty($data['ec_id'])) {
            $this->error('进件通道ID为空');
        }

        $user = \app\admin\model\User::find($data['user_id']);
        if (empty($user)) {
            $this->error('用户不存在');
        }

        \think\facade\Db::startTrans();

        $request = new \app\common\service\entry\EntryRequest();
        $request->setUser($user);

        if ($data['charge'] == '0') {
            $request->withoutDeduction();
        }
        if ($data['is_new_merchant'] == 1) {
            $request->createMerchant($data);
        }

        $channel = \app\admin\model\EntryChannel::find($data['ec_id']);
        if (empty($channel)) {
            $this->error('进件通道不存在');
        }
        $request->setChannel($channel);

        try {
            $service = new \app\common\service\EntryService($request);
            $result  = $service->apply();
            \think\facade\Db::commit();
        } catch (\Throwable $e) {
            \think\facade\Db::rollback();
            $this->error($e->getMessage());
            return;
        }

        $this->success('ok', $result);
    }

    public function edit(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('进件记录ID为空');
        }

        $row = $this->model->with('merchant')->find($id);
        if (empty($row)) {
            $this->error('进件记录不存在');
        }

        if (!$this->request->isPost()) {
            if (!empty($row['merchant'])) {
                $row['merchant_id'] = $row['merchant']['merchant_id'] ?? '';
                $row['app_id']      = $row['merchant']['config']['app_id'] ?? '';
            }
            $this->success('ok', ['row' => $row]);
            return;
        }

        $data = $this->request->post();
        $user = \app\admin\model\User::find($data['user_id']);
        if (empty($user)) {
            $this->error('用户不存在');
        }

        $isDraft = 0;
        if (isset($data['draft']) && $data['draft'] == 1) {
            $isDraft = 1;
        }

        try {
            if (isset($data['entryInfo'])) {
                if (empty($data['apply_data'])) {
                    throw new \Exception('进件资料为空');
                }

                $channel = \app\admin\model\EntryChannel::find($data['ec_id']);
                if (empty($channel)) {
                    throw new \Exception('进件通道不存在');
                }

                $request = new \app\common\service\entry\EntryRequest();
                $request->setUser($user);
                $request->setData($data['apply_data']);
                $request->setChannel($channel);

                if ($isDraft == 0) {
                    $request->asDraft();
                }

                $service = new \app\common\service\EntryService($request);
                $service->editData($row);
            } else {
                $row->save([
                    'user_id'      => $data['user_id'],
                    'status'       => $data['status'],
                    'audit_result' => $data['audit_result'] ?? '',
                ]);

                if (!empty($row->merchant)) {
                    $row->merchant->user_id = $data['user_id'];
                    $row->merchant->save();
                }

                if (!empty($data['app_id'])) {
                    if (empty($row['merchant']) || $row['status'] != '8') {
                        throw new \Exception('无法保持APPID，商户未完成进件');
                    }
                    $merchant = \app\admin\model\merchant\Merchant::find($row['merchant']['id']);
                    $config = $merchant->config;
                    $config['app_id'] = $data['app_id'];
                    $merchant->config = $config;
                    $merchant->save();
                }
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success($isDraft != 1 ? '提交成功' : '保存成功', $row);
    }

    public function del(): void
    {
        $where = [];
        $adminIds = $this->getDataLimitAdminIds();
        if ($adminIds) {
            $where[$this->dataLimitField] = ['in', $adminIds];
        }
        $ids = $this->request->param('ids/a');
        $where[$this->model->getPk()] = ['in', $ids];
        $list = $this->model->where($where)->select();
        $count = 0;
        try {
            foreach ($list as $row) {
                $request = new \app\common\service\entry\EntryRequest();
                $service = new \app\common\service\EntryService($request);
                $service->delete($row);
                $count++;
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success('删除成功');
            return;
        }
        $this->error(__('No rows were deleted'));
    }

    public function getapplyinfo(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('进件记录ID为空');
        }

        $row = \app\admin\model\merchant\MerchantEntry::find($id);
        if (empty($row)) {
            $this->error('进件记录不存在');
        }

        try {
            $request = new \app\common\service\entry\EntryRequest();
            $request->setUser($row->user);

            $channel = \app\admin\model\EntryChannel::find($row->ec_id);
            if (empty($channel)) {
                throw new \Exception('进件通道不存在');
            }
            $request->setChannel($channel);

            $service = new \app\common\service\EntryService($request);
            $info    = $service->queryStatus($row);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->success('ok', $info);
    }

    public function getentrytype(): void
    {
        $list = config('entry.channel_type');
        $this->success('ok', ['list' => $list]);
    }

}

<?php

// 还原自 Swoole Compiler 加密文件: SubscribeGroup.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/user/SubscribeGroup.php

namespace app\admin\controller\user;

use Throwable;

class SubscribeGroup extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $defaultSortField = 'weigh,desc';
    protected string|array $preExcludeFields = ['id', 'channel_types'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\user\SubscribeGroup();
    }

    /**
     * 添加（traits/Backend::add 定制版，opcode 真值差异：
     * $channelTypes = $data['channel_types'] ?? []; unset($data['channel_types']) 后 excludeFields；
     * save 成功且 $channelTypes 非空 → saveGroupChannels($this->model->id, $channelTypes)，随后统一 commit）
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $channelTypes = $data['channel_types'] ?? [];
            unset($data['channel_types']);
            $data = $this->excludeFields($data);

            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                $data[$this->dataLimitField] = $this->auth->id;
            }

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace('\\model\\', '\\validate\\', get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                $result = $this->model->save($data);
                if ($result !== false) {
                    if (!empty($channelTypes)) {
                        $this->saveGroupChannels($this->model->id, $channelTypes);
                    }
                }
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 编辑（traits/Backend::edit 定制版，opcode 真值差异：
     * $row = model->with(['channelTypes'])->find($id)；非 POST 返回 visible(['id','name','desc','status','weigh'])->toArray()；
     * POST 保存成功后 saveGroupChannels($row[$pk], $channelTypes)，再统一 commit）
     */
    public function edit(): void
    {
        $pk = $this->model->getPk();
        $id = $this->request->param($pk);
        $row = $this->model->with(['channelTypes'])->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $channelTypes = $data['channel_types'] ?? [];
            unset($data['channel_types']);
            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace('\\model\\', '\\validate\\', get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('edit');
                        $data[$pk] = $row[$pk];
                        $validate->check($data);
                    }
                }
                $result = $this->model->save($data);
                if ($result !== false) {
                    $this->saveGroupChannels($row[$pk], $channelTypes);
                }
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $row = $row->visible(['id', 'name', 'desc', 'status', 'weigh'])->toArray();
        $this->success('', [
            'row' => $row,
        ]);
    }

    /**
     * 分组通道列表（opcode 真值：channel\Type 全表 where(status,1)->order(weigh,desc)；
     * SubscribeGroupChannel with(subscribeGroup)，$groupId>0 时排除自身 group_id(<>)；
     * $map[channel_type] = subscribeGroup.name ?? ''；再遍历 $channelTypes 打 assigned/assigned_group 标）
     */
    public function getchannels(): void
    {
        $groupId = (int)$this->request->param('group_id', 0);
        $channelTypeModel = new \app\admin\model\channel\Type();
        $channelTypes = $channelTypeModel->where('status', 1)
            ->order('weigh', 'desc')
            ->select()
            ->toArray();

        $groupChannels = (new \app\admin\model\user\SubscribeGroupChannel())->with(['subscribeGroup']);
        if ($groupId) {
            $groupChannels = $groupChannels->where('group_id', '<>', $groupId);
        }
        $groupChannels = $groupChannels->select()->toArray();

        $map = [];
        foreach ($groupChannels as $item) {
            $map[$item['channel_type']] = $item['subscribeGroup']['name'] ?? '';
        }
        foreach ($channelTypes as &$item) {
            $type = $item['type'];
            $item['assigned']       = isset($map[$type]);
            $item['assigned_group'] = $map[$type] ?? '';
        }
        unset($item);

        $this->success('', [
            'list' => $channelTypes,
            'map'  => $map,
        ]);
    }

    /**
     * 保存分组通道（opcode 真值：先删该组 SubscribeGroupChannel；channelTypes 空→return；
     * 其他组占用检查 whereIn(channel_type)+where(group_id,'<>',groupId)，
     * 命中即 throw new \Exception('以下通道已被其他分组占用，无法重复分配：' . implode('、', array_map(fn($c)=>$c['channel_type'], $groupChannels)))；
     * 过滤空值后 insertAll([group_id, channel_type])）
     */
    protected function saveGroupChannels(int|null $groupId = null, array|null $channelTypes = null): void
    {
        $groupChannelModel = new \app\admin\model\user\SubscribeGroupChannel();
        $groupChannelModel->where('group_id', $groupId)->delete();

        if (empty($channelTypes)) {
            return;
        }

        $groupChannels = $groupChannelModel
            ->with(['subscribeGroup'])
            ->whereIn('channel_type', $channelTypes)
            ->where('group_id', '<>', $groupId)
            ->select()
            ->toArray();

        if (!empty($groupChannels)) {
            $types = array_map(function ($channel) {
                return $channel['channel_type'];
            }, $groupChannels);
            throw new \Exception('以下通道已被其他分组占用，无法重复分配：' . implode('、', $types));
        }

        $rows = [];
        foreach ($channelTypes as $channelType) {
            if (empty($channelType)) {
                continue;
            }
            $rows[] = [
                'group_id'     => $groupId,
                'channel_type' => $channelType,
            ];
        }

        if (!empty($rows)) {
            $groupChannelModel->insertAll($rows);
        }
    }

    /**
     * 删除（opcode 真值：ids/a 批量删除；逐行：组内 Subscribe 全部 save(['group_id'=>0]) 后
     * SubscribeGroupChannel::where(group_id,$row['id'])->delete()、$row->delete() 计数；
     * commit/catch(rollback+error)；$count 真 success(__('Deleted successfully')) 否则 error(__('No rows were deleted'))）
     */
    public function del(): void
    {
        $where = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }
        $ids = $this->request->param('ids/a', []);
        $pk  = $this->model->getPk();
        $where[] = [$pk, 'in', $ids];
        $list  = $this->model->where($where)->select();
        $count = 0;
        $this->model->startTrans();
        try {
            foreach ($list as $row) {
                $subscribes = \app\admin\model\user\Subscribe::where('group_id', $row['id'])->select();
                foreach ($subscribes as $subscribe) {
                    $subscribe->save(['group_id' => 0]);
                }
                \app\admin\model\user\SubscribeGroupChannel::where('group_id', $row['id'])->delete();
                $row->delete();
                $count++;
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }

}

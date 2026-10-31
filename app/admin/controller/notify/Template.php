<?php

// 还原自 Swoole Compiler 加密文件: Template.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/notify/Template.php

namespace app\admin\controller\notify;

class Template extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', 'create_time', 'update_time', 'vars'];
    protected string|array $quickSearchField = ['id', 'event', 'name', 'title'];
    protected bool $modelSceneValidate = true;
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\notify\Template();
        $this->request->filter('clean_xss');
    }
    public function index(): void
    {
        $select = $this->request->param('select');
        if ($select) {
            $this->select();
        }

        $rows  = [];
        $slots = [];
        try {
            $rows  = $this->buildNotifyConfigRows();
            $slots = $this->buildChannelTypeSlotBlueprint();
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }

        $this->success('', [
            'list'               => $rows,
            'total'              => count($rows),
            'remark'             => get_route_remark(),
            'channel_type_slots' => $slots,
        ]);
    }

    public function add(): void
    {
        if (!$this->request->isPost()) {
            $this->error(__('Parameter error'));
        }

        $data = $this->request->post();
        if (!$data) {
            $this->error(__('Parameter %s can not be empty', ['']));
        }

        $assertMsg = $this->assertEventAndChannelTypeInConfig($data);
        if ($assertMsg) {
            $this->error($assertMsg);
        }
        $this->applyTemplateNameFromConfig($data);
        $data = $this->excludeFields($data);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $data[$this->dataLimitField] = $this->auth->id;
        }

        $result = false;
        $this->model->startTrans();
        try {
            if ($this->modelValidate) {
                $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                if (class_exists($validate)) {
                    $validate = new $validate();
                    if ($this->modelSceneValidate) $validate->scene('add');
                    $validate->check($data);
                }
            }
            $result = $this->model->save($data);
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
        $this->error(__('Parameter error'));
    }

    public function setstatus(): void
    {
        if (!$this->request->isPost()) {
            $this->error(__('Parameter error'));
        }
        $id     = (int)$this->request->post('id/d', 0);
        $status = (int)$this->request->post('status/d', -1);
        if ($id < 1) {
            $this->error(__('Parameter error'));
        }
        if (!in_array($status, [0, 1], true)) {
            $this->error(__('状态值无效'));
        }

        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $events = array_column((array)config('notify.template', []), 'event');
        if (!in_array((string)$row['event'], $events, true)) {
            $this->error(__('事件不在系统预定义通知列表中'));
        }

        try {
            $row->save(['status' => $status]);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('Update successful'));
    }

    public function edit(): void
    {
        $pk = $this->model->getPk();
        $id = $this->request->param($pk);
        $row = null;
        if ($id) {
            $row = $this->model->find($id);
        }

        $event = (string)$this->request->param('event', '');
        $nType = (string)$this->request->param('notify_type', '');
        if ($event !== '' && $nType !== '') {
            $nEvent = \app\admin\model\notify\Template::normalizeNotifyType($nType);
            $row = $this->findModelRowByEventAndNotifyType($event, $nEvent);
        }

        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if (!$this->request->isPost()) {
            $arr                = $row->toArray();
            $arr['vars']        = $this->varsFromConfigForEvent((string)($row['event'] ?? ''));
            $arr['notify_type'] = \app\admin\model\notify\Template::normalizeNotifyType((string)($row['notify_type'] ?? ''));
            $this->success('', ['row' => $arr]);
        }

        $data = $this->request->post();
        if (!$data) {
            $this->error(__('Parameter %s can not be empty', ['']));
        }
        $assertMsg = $this->assertEventAndChannelTypeInConfig($data);
        if ($assertMsg) {
            $this->error($assertMsg);
        }
        $this->applyTemplateNameFromConfig($data);
        $data = $this->excludeFields($data);

        $result = false;
        $dbRow  = null;
        $hasRow = false;
        $this->model->startTrans();
        try {
            $event  = (string)($data['event'] ?? '');
            $nEvent = \app\admin\model\notify\Template::normalizeNotifyType((string)($data['notify_type'] ?? ''));
            $dbRow  = $this->findModelRowByEventAndNotifyType($event, $nEvent);
            $hasRow = (bool)$dbRow;

            if ($this->modelValidate) {
                $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                if (class_exists($validate)) {
                    $validate = new $validate();
                    if ($dbRow) {
                        $validate->scene('edit');
                    } else {
                        $validate->scene('add');
                    }
                    $validate->check($data);
                }
            }

            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                if ($dbRow) {
                    if (!($data[$this->dataLimitField] ?? null)) {
                        $data[$this->dataLimitField] = $dbRow[$this->dataLimitField];
                    }
                } else {
                    $data[$this->dataLimitField] = $this->auth->id;
                }
            }

            if ($dbRow) {
                if ((int)($dbRow[$pk] ?? 0) !== (int)($row[$pk] ?? 0)) {
                    $this->model->rollback();
                    $this->error(__('与当前编辑记录不一致'));
                }
                $data[$pk] = $dbRow[$pk];
                unset($data['event'], $data['notify_type']);
                $result = $row->save($data);
            } else {
                $result = (new \app\admin\model\notify\Template())->save($data);
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }

        if ($result !== false) {
            if ($hasRow) {
                $this->success(__('Update successful'));
            } else {
                $this->success(__('Added successfully'));
            }
        } else {
            $this->error(__('No rows updated'));
        }
    }    protected function findModelRowByEventAndNotifyType(string|null $event = null, string|null $notifyTypeNorm = null)
    {
        if ($event === '' || $notifyTypeNorm === '') {
            return null;
        }
        $query = \app\admin\model\notify\Template::where('event', $event);
        if ($notifyTypeNorm === 'ems') {
            $query = $query->whereIn('notify_type', ['ems', 'email']);
        } else {
            $query = $query->where('notify_type', $notifyTypeNorm);
        }
        return $query->order('id', 'desc')->find();
    }
    protected function varsFromConfigForEvent(string|null $event = null): array
    {
        $config = config('notify.template', []);
        foreach ($config as $item) {
            if ($event !== (string)($item['event'] ?? '')) {
                continue;
            }
            $vars = $item['vars'] ?? [];
            if (is_array($vars)) {
                return $vars;
            }
            return [];
        }
        return [];
    }
    protected function getNotifyChannelTypeDefinitions(): array
    {
        $types = (array)config('notify.type', []);
        if (empty($types)) {
            $types = (array)config('notify.channel_types', []);
        }
        if (empty($types)) {
            $types = [
                ['name' => '短信', 'type' => 'sms'],
                ['name' => '邮件', 'type' => 'ems'],
            ];
        }
        return $types;
    }
    protected function buildChannelTypeSlotBlueprint(): array
    {
        $blueprint = [];
        $defs = $this->getNotifyChannelTypeDefinitions();
        foreach ($defs as $def) {
            $type = (string)($def['type'] ?? '');
            if ($type === '') {
                continue;
            }
            $type = \app\admin\model\notify\Template::normalizeNotifyType($type);
            $blueprint[] = [
                'type'        => $type,
                'type_label'  => (string)($def['name'] ?? ''),
                'notify_type' => $type,
                'template'    => null,
            ];
        }
        return $blueprint;
    }
    protected function buildNotifyConfigRows(): array
    {
        $config = (array)config('notify.template', []);
        $defs   = $this->getNotifyChannelTypeDefinitions();
        $events = array_filter(array_column($config, 'event'));
        $rows   = $this->model->whereIn('event', $events)->order('id', 'asc')->select()->toArray();
        $map    = $this->mapDbRowsByEventAndNotifyType($rows);

        $list = [];
        foreach ($config as $item) {
            $event = (string)($item['event'] ?? '');
            if ($event === '') {
                continue;
            }
            $slots = [];
            foreach ($defs as $def) {
                $type = (string)($def['type'] ?? '');
                if ($type === '') {
                    continue;
                }
                $type    = \app\admin\model\notify\Template::normalizeNotifyType($type);
                $slotKey = $this->templateSlotKey($event, $type);
                $row     = $map[$slotKey] ?? null;
                if ($row) {
                    $row = $this->hydrateContentRow($row);
                }
                $slots[] = [
                    'type'        => $type,
                    'type_label'  => (string)($def['name'] ?? ''),
                    'notify_type' => $type,
                    'template'    => $row,
                ];
            }
            $list[] = [
                'id'       => $event,
                'event'    => $event,
                'name'     => (string)($item['name'] ?? ''),
                'vars'     => $item['vars'] ?? [],
                'channels' => $slots,
            ];
        }
        return $list;
    }
    protected function mapDbRowsByEventAndNotifyType(array|null $allDbTemplates = null): array
    {
        $map = [];
        foreach ($allDbTemplates as $row) {
            $event = (string)($row['event'] ?? '');
            $nType = \app\admin\model\notify\Template::normalizeNotifyType((string)($row['notify_type'] ?? ''));
            if ($event === '' || $nType === '') {
                continue;
            }
            $key = $this->templateSlotKey($event, $nType);
            if (!isset($map[$key])) {
                $map[$key] = $row;
            } else {
                if ((int)($map[$key]['id'] ?? 0) === (int)($row['id'] ?? 0)) {
                    $map[$key] = $row;
                }
            }
        }
        return $map;
    }
    protected function templateSlotKey(string|null $event = null, string|null $canonicalNotifyType = null): string
    {
        return $event . '|' . $canonicalNotifyType;
    }
    protected function hydrateContentRow(array|null $dbData = null): array
    {
        if (array_key_exists('content', $dbData) && is_string($dbData['content']) && $dbData['content'] !== '') {
            $plain   = strip_tags($dbData['content']);
            $len     = mb_strlen($plain, 'UTF-8');
            $preview = mb_substr($plain, 0, 80, 'UTF-8');
            if (80 < $len) {
                $suffix = '…';
            } else {
                $suffix = '';
            }
            $dbData['content_preview'] = $preview . $suffix;
        } else {
            $dbData['content_preview'] = '';
        }
        if (array_key_exists('status', $dbData)) {
            $dbData['status'] = (string)$dbData['status'];
        }
        return $dbData;
    }
    protected function assertEventAndChannelTypeInConfig(array|null $data = null): string
    {
        $event = (string)($data['event'] ?? '');
        $nType = (string)($data['notify_type'] ?? '');
        if ($event === '' || $nType === '') {
            return '事件或通知类型不能为空';
        }
        $events = array_column((array)config('notify.template', []), 'event');
        if (!in_array($event, $events, true)) {
            return '事件不在系统预定义通知列表中';
        }
        $nEvent = \app\admin\model\notify\Template::normalizeNotifyType($nType);
        $defs   = $this->getNotifyChannelTypeDefinitions();
        $nTypes = array_values(array_unique(array_filter(array_map(function ($c) {
            $type = (string)($c['type'] ?? '');
            if ($type !== '') {
                return \app\admin\model\notify\Template::normalizeNotifyType($type);
            }
            return '';
        }, $defs))));
        if (empty($nTypes)) {
            $nTypes = ['sms', 'ems'];
        }
        if (!in_array($nEvent, $nTypes, true)) {
            return '通知类型不在系统配置中';
        }
        return '';
    }
    protected function applyTemplateNameFromConfig(array|null &$data = null): void
    {
        $event  = (string)($data['event'] ?? '');
        $config = config('notify.template', []);
        foreach ($config as $item) {
            if ($event === ($item['event'] ?? '')) {
                $data['name'] = (string)($item['name'] ?? '');
                return;
            }
        }
    }
}

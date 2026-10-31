<?php

// 还原自 Swoole Compiler 加密文件: Rule.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/auth/Rule.php

namespace app\admin\controller\auth;

class Rule extends \app\common\controller\Backend {
    protected string|array $preExcludeFields = ['create_time', 'update_time'];
    protected string|array $defaultSortField = ['weigh' => 'desc'];
    protected string|array $quickSearchField = 'title';
    protected object $model;
    protected \ba\Tree $tree;
    protected array $initValue;
    protected string $keyword;
    protected bool $assembleTree;
    protected bool $modelValidate = false;

    public function initialize(): void
    {
        parent::initialize();
        $this->request->filter('clean_xss');

        $this->model = new \app\admin\model\AdminRule();
        $this->tree  = \ba\Tree::instance();

        $isTree          = $this->request->param('isTree', true);
        $this->initValue = $this->request->get('initValue/a', []);
        $this->initValue = array_filter($this->initValue);
        $this->keyword   = $this->request->request('quickSearch', '');

        $this->assembleTree = $isTree && !$this->initValue;
    }

    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        $this->success('', [
            'list'   => $this->getMenus(),
            'remark' => get_route_remark(),
        ]);
    }

    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

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
                        if ($this->modelSceneValidate) {
                            $validate->scene('add');
                        }
                        $validate->check($data);
                    }
                }
                $result = $this->model->save($data);

                if (!empty($data['pid'])) {
                    // 检查有那些分组可以拥有新增菜单的权限
                    $groups = \app\admin\model\AdminGroup::where('rules', '<>', '*')->select();
                    foreach ($groups as $group) {
                        $rules = explode(',', $group->rules);
                        if (in_array($data['pid'], $rules) && !in_array($this->model->id, $rules)) {
                            $rules[]      = $this->model->id;
                            $group->rules = implode(',', $rules);
                            $group->save();
                        }
                    }
                }

                $this->model->commit();
            } catch (\Throwable $e) {
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

    public function edit(): void
    {
        $id = $this->request->param($this->model->getPk());
        $row = $this->model->find($id);
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

            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) {
                            $validate->scene('edit');
                        }
                        $validate->check($data);
                    }
                }

                if (isset($data['pid']) && $data['pid'] > 0) {
                    // 检查选中的父级菜单是否在自己的权限范围内，且不能选择自己和自己的下级（会导致死循环）
                    $parent = $this->model->where('id', $data['pid'])->find();
                    if ($parent['pid'] == $row['id']) {
                        $parent->pid = 0;
                        $parent->save();
                    }
                }

                $result = $row->save($data);
                $this->model->commit();
            } catch (\Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }

            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $this->success('', [
            'row' => $row
        ]);
    }

    public function del(): void
    {
        $ids     = $this->request->param('ids/a', []);
        $subData = $this->model->where('pid', 'in', $ids)->column('pid', 'id');
        foreach ($subData as $key => $subDatum) {
            if (!in_array($key, $ids)) {
                $this->error(__('Please delete the child element first, or use batch deletion'));
            }
        }

        parent::del();
    }

    public function select(): void
    {
        $data = $this->getMenus([['type', 'in', ['menu_dir', 'menu']]]);

        if ($this->assembleTree) {
            $data = $this->tree->assembleTree($this->tree->getTreeArray($data, 'title'));
        }

        $this->success('', [
            'options' => $data,
        ]);
    }

    protected function getMenus($where = []): array
    {
        $pk      = $this->model->getPk();
        $initKey = $this->request->get('initKey/s', $pk);
        $ids     = $this->auth->getRuleIds();

        if (!in_array('*', $ids)) {
            $where[] = [$pk, 'in', $ids];
        }

        if ($this->keyword) {
            $keyword = explode(' ', $this->keyword);
            foreach ($keyword as $item) {
                $where[] = [$this->quickSearchField, 'like', '%' . $item . '%'];
            }
        }

        if ($this->initValue) {
            $where[] = [$initKey, 'in', $this->initValue];
        }

        $rules = $this->model->where($where)->order($this->queryOrderBuilder())->select()->toArray();

        return $this->assembleTree ? $this->tree->assembleChild($rules) : $rules;
    }

}

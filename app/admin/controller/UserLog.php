<?php

// 还原自 Swoole Compiler 加密文件: UserLog.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/UserLog.php

namespace app\admin\controller;

class UserLog extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', null];
    protected array $withJoinTable = ['user'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\UserLog();
    }

    public function index(): void
    {
        // [opcode 对照] 加密原件本类覆写 index()（前次反编译整方法丢失，按 opcode 全量补齐，同 EntryChannel 先例）：
        // DECLARE_CONST(request)+YIELD_FROM(param)+NOP('select')+DO_FCALL+JMPZ→select()
        // YIELD_FROM(queryBuilder)+FETCH_LIST_R×4(QM_ASSIGN where/alias/limit/order)+FREE
        // model+withJoin(withJoinTable,withJoinType)+visible({'user':['username']})+alias($alias)
        // +where($where)+order($order)+paginate($limit)；success('',{'list':items,'total':total,'remark':get_route_remark()})
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->visible(['user' => ['username']])
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    public function cleardata(): void
    {
        // opcode: DECLARE_CONST(request)+YIELD_FROM(param)+NOP('time')+DO_FCALL+QM_ASSIGN → $time = request->param('time')
        $time = $this->request->param('time');
        // opcode: ISSET_ISEMPTY_CV(EMPTY)+JMPZ；空参 → JMP op1=832 → (10*32+832)/32=36 直达 where/delete（清空全部）
        if (empty($time)) {
            // opcode: 隐藏 INIT 'app\admin\controller\time'（命名空间回退）+DO_FCALL_BY_NAME+QM_ASSIGN → time()
            $clear_time = time();
        // opcode: JMPZ(is_numeric)+SEND($time,1)+DO_FCALL_BY_NAME，假→JMP op2=576 → (14*32+576)/32=32 strtotime 分支
        } elseif (is_numeric($time)) {
            // opcode: strlen($time)+IS_IDENTICAL 13+JMPZ→26
            if (strlen($time) === 13) {
                // opcode: DIV($time,1000)+intval+QM_ASSIGN+JMP op1=160 → (25*32+160)/32=30 汇合
                $clear_time = intval($time / 1000);
            } else {
                // opcode: intval($time)+QM_ASSIGN
                $clear_time = intval($time);
            }
        } else {
            // opcode: strtotime($time)+QM_ASSIGN
            $clear_time = strtotime($time);
        }
        // opcode: DECLARE_CONST(model)+YIELD_FROM(where)+NOP('create_time')+NOP('<')+SEND($clear_time,3)
        //         +DO_FCALL+YIELD_FROM(delete)+DO_FCALL
        $this->model->where('create_time', '<', $clear_time)->delete();
        // opcode: YIELD_FROM(success)+NOP('清理成功')+DO_FCALL+RETURN
        $this->success('清理成功');
    }

}

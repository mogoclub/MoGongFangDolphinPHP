<?php


namespace app\demo\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use think\Db;

class Temp extends Admin{
    public function index(){
        // 获取查询和排序规则
        $map = $this->getMap();
        $order = $this->getOrder();
        $data = Db::table('db_xiaoan_post',true) //TODO 需要修改
             ->view('xiaoan_post',['title'],'xiaoan_post.id=xiaoan_comment.post_id')
            ->where('type','skill')
            ->where($map)
            ->order($order)
            ->order('id desc')
            ->paginate()->each(function($item){
                // 对分页数据可以做额外处理
                return $item;
            });
        return ZBuilder::make('table')
            ->setTableName('xiaoan_post')  //需要修改 这里修改正确可以直接修改字段 删除
            ->addColumns([
                ['id','id'],
                ['pic','配图','picture'],
                ['title','标题'],
                ['content','内容'],
                ['skill_cate','分类','text.edit'],
                ['visible','是否可见','switch'],
                ['sort','排序','number'],
                ['create_time','创建时间','datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('add', [],[])
            ->addRightButton('edit',[],[])
            ->addTopButtons(['delete'])
            ->setRowList($data)
            ->addFilter(['title' => 'admin_action', 'username'])
            ->setSearch(['id' => 'ID', 'title' => '标题'])
            ->fetch();
    }
    public function add(){
        if($this->request->isPost()){
            // 第一步 获取数据 如果后台面向大众 需要用only方法
            $data = request()->post();
            // 第二部 验证数据
            $result = $this->validate($data,'Skill');
            if($result!==true) $this->error($result);
            // 第三部 存储
            Post::createSkill($data);
            $this->success('保存成功', null, '_parent_reload');
        }
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'title', '标题'],
                ['image', 'pic', '配图'],
                ['textarea','content','内容'],
                ['select','cate_id','分类','',\app\xiaoan\model\Cate::where('type','skill')->column('id,name')],
            ])
            ->fetch();
    }
    public function edit($id=null){
        if($this->request->isPost()){
            $data = request()->post();
            $result = $this->validate($data,'Skill');
            if($result!==true) $this->error($result);
            Post::update($data);
            $this->success('保存成功', null, '_parent_reload');
        }
        $data = Post::get($id);
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden','id'],  // 多增加一个影藏ID 即可
                ['text', 'title', '标题'],
                ['image', 'pic', '配图'],
                ['textarea','content','内容'],
                ['select','cate_id','分类','',\app\xiaoan\model\Cate::where('type','skill')->column('id,name')],
            ])
            ->setFormData($data)
            ->fetch();
    }
}
<?php
namespace app\xiaoan\home;

use app\demo\model\Temp as TempModel; // 与控制器同名 重命名
//use app\demo\model\Post; 不重名就不需要重命名
class Temp extends Base {
    // 获取列表
    public function index($row=10){
        // 查询字段
        $map = $this->getMap();
        $Post = new TempModel();
        // 自定义where查询
        $map['type']='skill';
        $data =  $Post
            ->order('id desc')
            ->where($map)
            ->paginate($row)
            ->each(function($item){
                //对item增加额外字段
                $item['diy'] = 'diy';
                return $item;
            });
        $data = $data->toArray();
        // 增加额外数据
        $data['cate']  = $Post::column('skill_cate');
        foreach ($data['cate'] as $key=> &$item){
            if($item==null) unset($data['cate'][$key]);
        }
        return json($data);
    }
    // 保存内容
    public function save(){
        // 第一步 过滤字段
        $data = request()->only(['title', 'content','openid','pics']);
        // 第二部 验证字段
        $result = $this->validate($data,'Community');
        if(true!==$result){
            return mogo_error($result);
        }
        // 第三部 存储字段
        $res = TempModel::create($data);
        // 第四部 返回结果
        return json($res);
    }
    // 读取内容
    public function read($id){
        $data = TempModel::get($id);
        // 第一步 验存
        if(!$data) return mogo_error('帖子不存在');
        // 这里可以做一些处理
        return json($data);
    }
    // 删除内容
    public function delete($id){
        $data = TempModel::get($id);
        // 第一步 验存
        if(!$data) return mogo_error('帖子不存在');
        // 第二部 验证权限
        // 第三部 返回处理结果,便于调试
        $res=[
           'result'=> $data->delete(),
        ];
        return json($res);
    }
}
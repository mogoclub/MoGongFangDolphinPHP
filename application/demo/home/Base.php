<?php


namespace app\xiaoan\home;

use app\common\controller\Common;
use think\Controller;
use think\Request;
use app\xiaoan\model\User;
/**
 * 仪表盘控制器
 * @package app\cms\admin
 */
class Base extends Common {
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        // API做验证
//        // 验证token
//        $authorization = $this->request->header('authorization');
//        $token = str_replace('Bearer ','',$authorization);
//        if(empty($token)){
//            echo json_encode(['error'=>'invalid token']);
//            header("HTTP/1.1 404 Not Found");
//            header("Status: 404 Not Found");
//            header('Content-type: application/json');
//            exit();
//        }
//        // 验证存在
//        $user = User::get(['token'=>$token]);
//        if(!$user) {
//            echo json_encode(['error'=>$token]);
//            header("HTTP/1.1 404 Not Found");
//            header("Status: 404 Not Found");
//            header('Content-type: application/json');
//            exit;
//        }
//        define('OPENID',$user['openid']);
//        define('USERID',$user['id']);
    }
}
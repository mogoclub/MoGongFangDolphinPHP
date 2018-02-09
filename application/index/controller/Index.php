<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\index\controller;
use app\admin\model\Attachment as AttachmentModel;
use think\Hook;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Index extends Home
{
    public function index()
    {
        // 默认跳转模块
        if (config('home_default_module') != 'index') {
            $this->redirect(config('home_default_module'). '/index/index');
        }

        return json(['result'=>$this->request]);
    }

    /**
     * 文件上传接口
     * @param string $dir
     * @param string $from
     * @param string $module
     * @return string|\think\response\Json
     */
    public function upload($dir = 'images', $from = '', $module = 'xiaoan')
    {
        $file = $this->request->file('file');
        if(!(isset($file))) return json('无文件上传',422);
        // 临时取消执行时间限制
        set_time_limit(0);
//        halt($this->request);
        if ($dir == '') $this->error('没有指定上传目录');
        if ($from == 'ueditor') return $this->ueditor();
        if ($from == 'jcrop') return $this->jcrop();
        return $this->saveFile($dir, $from, $module);
    }
    // 上传
    private function saveFile($dir = '', $from = '', $module = '')
    {
        // 附件大小限制
        $size_limit = $dir == 'images' ? config('upload_image_size') : config('upload_file_size');
        $size_limit = $size_limit * 1024;
        // 附件类型限制
        $ext_limit = $dir == 'images' ? config('upload_image_ext') : config('upload_file_ext');
        $ext_limit = $ext_limit != '' ? parse_attr($ext_limit) : '';

        // 获取附件数据
        $callback = '';
        switch ($from) {
            case 'editormd':
                $file_input_name = 'editormd-image-file';
                break;
            case 'ckeditor':
                $file_input_name = 'upload';
                $callback = $this->request->get('CKEditorFuncNum');
                break;
            case 'ueditor_scrawl':
                return $this->saveScrawl();
                break;
            default:
                $file_input_name = 'file';
        }
        $file = $this->request->file($file_input_name);

        // 判断附件是否已存在
        if ($file_exists = AttachmentModel::get(['md5' => $file->hash('md5')])) {

            if ($file_exists['driver'] == 'local') {
                $file_path = PUBLIC_PATH. $file_exists['path'];
            } else {
                $file_path = $file_exists['path'];
            }
            switch ($from) {
                case 'wangeditor':
                    return $file_path;
                    break;
                case 'ueditor':
                    return json([
                        "state" => "SUCCESS",          // 上传状态，上传成功时必须返回"SUCCESS"
                        "url"   => $file_path, // 返回的地址
                        "title" => $file_exists['name'], // 附件名
                    ]);
                    break;
                case 'editormd':
                    return json([
                        "success" => 1,
                        "message" => '上传成功',
                        "url"     => $file_path,
                    ]);
                    break;
                case 'ckeditor':
                    return ck_js($callback, $file_path);
                    break;
                default:
                    return json([
                        'code'   => 1,
                        'info'   => '上传成功',
                        'class'  => 'success',
                        'id'     => $file_exists['id'],
                        'path'   => $file_path
                    ]);
            }
        }

        // 判断附件大小是否超过限制
        if ($size_limit > 0 && ($file->getInfo('size') > $size_limit)) {
            switch ($from) {
                case 'wangeditor':
                    return "error|附件过大";
                    break;
                case 'ueditor':
                    return json(['state' => '附件过大']);
                    break;
                case 'editormd':
                    return json(["success" => 0, "message" => '附件过大']);
                    break;
                case 'ckeditor':
                    return ck_js($callback, '', '附件过大');
                    break;
                default:
                    return json([
                        'code'   => 0,
                        'class'  => 'danger',
                        'info'   => '附件过大'
                    ]);
            }
        }

        // 判断附件格式是否符合
        $file_name = $file->getInfo('name');
        $file_ext  = strtolower(substr($file_name, strrpos($file_name, '.')+1));
        $error_msg = '';
        if ($ext_limit == '') {
            $error_msg = '获取文件信息失败！';
        }
        if ($file->getMime() == 'text/x-php' || $file->getMime() == 'text/html') {
            $error_msg = '禁止上传非法文件！';
        }
        if (preg_grep("/php/i", $ext_limit)) {
            $error_msg = '禁止上传非法文件！';
        }
        if (!preg_grep("/$file_ext/i", $ext_limit)) {
            $error_msg = '附件类型不正确！';
        }

        if ($error_msg != '') {
            switch ($from) {
                case 'wangeditor':
                    return "error|{$error_msg}";
                    break;
                case 'ueditor':
                    return json(['state' => $error_msg]);
                    break;
                case 'editormd':
                    return json(["success" => 0, "message" => $error_msg]);
                    break;
                case 'ckeditor':
                    return ck_js($callback, '', $error_msg);
                    break;
                default:
                    return json([
                        'code'   => 0,
                        'class'  => 'danger',
                        'info'   => $error_msg
                    ]);
            }
        }

        // 附件上传钩子，用于第三方文件上传扩展
        if (config('upload_driver') != 'local') {
            $hook_result = Hook::listen('upload_attachment', $file, ['from' => $from, 'module' => $module], true);
            if (false !== $hook_result) {
                return $hook_result;
            }
        }

        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move(config('upload_path') . DS . $dir);

        if($info){
            // 水印功能
            if ($dir == 'images' && config('upload_thumb_water') == 1 && config('upload_thumb_water_pic') > 0) {
                $this->create_water($info->getRealPath());
            }

            // 缩略图路径
            $thumb_path_name = '';
            // 生成缩略图
            if ($dir == 'images' && config('upload_image_thumb') != '') {
                $thumb_path_name = $this->create_thumb($info, $info->getPathInfo()->getfileName(), $info->getFilename());
            }

            // 获取附件信息
            $file_info = [
                'uid'    => '1',
                'name'   => $file->getInfo('name'),
                'mime'   => $file->getInfo('type'),
                'path'   => 'uploads/' . $dir . '/' . str_replace('\\', '/', $info->getSaveName()),
                'ext'    => $info->getExtension(),
                'size'   => $info->getSize(),
                'md5'    => $info->hash('md5'),
                'sha1'   => $info->hash('sha1'),
                'thumb'  => $thumb_path_name,
                'module' => $module
            ];


            // 写入数据库
            if ($file_add = AttachmentModel::create($file_info)) {
                $file_path = PUBLIC_PATH. $file_info['path'];
                switch ($from) {
                    case 'wangeditor':
                        return $file_path;
                        break;
                    case 'ueditor':
                        return json([
                            "state" => "SUCCESS",          // 上传状态，上传成功时必须返回"SUCCESS"
                            "url"   => $file_path, // 返回的地址
                            "title" => $file_info['name'], // 附件名
                        ]);
                        break;
                    case 'editormd':
                        return json([
                            "success" => 1,
                            "message" => '上传成功',
                            "url"     => $file_path,
                        ]);
                        break;
                    case 'ckeditor':
                        return ck_js($callback, $file_path);
                        break;
                    default:
                        return json([
                            'code'   => 1,
                            'info'   => '上传成功',
                            'class'  => 'success',
                            'id'     => $file_add['id'],
                            'path'   => $file_path
                        ]);
                }
            } else {
                switch ($from) {
                    case 'wangeditor':
                        return "error|上传失败";
                        break;
                    case 'ueditor':
                        return json(['state' => '上传失败']);
                        break;
                    case 'editormd':
                        return json(["success" => 0, "message" => '上传失败']);
                        break;
                    case 'ckeditor':
                        return ck_js($callback, '', '上传失败');
                        break;
                    default:
                        return json(['code' => 0, 'class' => 'danger', 'info' => '上传失败']);
                }
            }
        }else{
            switch ($from) {
                case 'wangeditor':
                    return "error|".$file->getError();
                    break;
                case 'ueditor':
                    return json(['state' => $file->getError()]);
                    break;
                case 'editormd':
                    return json(["success" => 0, "message" => $file->getError()]);
                    break;
                case 'ckeditor':
                    return ck_js($callback, '', $file->getError());
                    break;
                default:
                    return json(['code' => 0, 'class' => 'danger', 'info' => $file->getError()]);
            }
        }
    }
}

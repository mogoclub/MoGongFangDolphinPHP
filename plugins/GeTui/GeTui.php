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

namespace plugins\GeTui;

require ROOT_PATH.'plugins/GeTui/SDK/demo.php';
use app\common\controller\Plugin;

require_once(ROOT_PATH . 'plugins/GeTui/SDK/' . 'IGt.Push.php');
require_once(ROOT_PATH . 'plugins/GeTui/SDK/' . 'igetui/utils/AppConditions.php');


class GeTui extends Plugin
{
    /**
     * @var array 插件信息
     */
    public $info = [
        // 插件名[必填]
        'name'        => 'GeTui',
        // 插件标题[必填]
        'title'       => '个推推送插件',
        // 插件唯一标识[必填],格式：插件名.开发者标识.plugin
        'identifier'  => 'ge_tui.arh.plugin',
        // 插件图标[选填]
        'icon'        => 'fa fa-fw fa-share',
        // 插件描述[选填]
        'description' => '需要在配置中配置ak sk 等',
        // 插件作者[必填]
        'author'      => '滕亚庆',
        // 作者主页[选填]
        'author_url'  => 'http://www.femirror.com',
        // 插件版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
        'version'     => '1.0.0',
        // 是否有后台管理功能[选填]
        'admin'       => '0',
    ];
    public $HOST = 'http://sdk.open.api.igexin.com/apiex.htm';


    //  消息指定
    public function notification($CIDS,$title,$text,$json){
        $template = $this->IGtNotificationTemplateDemo($title,$text,$json);
        return $this->ListMessageParcel($CIDS,$template);

    }
    // 消息所有
    public function notificationAll($title,$text,$json){
        $template = $this->IGtNotificationTemplateDemo($title,$text,$json);
        return $this->AppMessageParcel($template);
    }

    //透传指定
    public function transmission($CID=[],$json = [])
    {
        $template = $this->IGtTransmissionTemplateDemo($json);
        return $this->ListMessageParcel($CID,$template);
    }

    // 透传所有
    public function transmissionAll($json = []){
        $template = $this->IGtTransmissionTemplateDemo($json);
        return $this->AppMessageParcel($template);
    }


    // 指定人
    public function ListMessageParcel($CIDS= [],$template){

        $Config = plugin_config('GeTui');
        $igt = new \IGeTui($this->HOST,$Config['AppKey'],$Config['MasterSecret']);


        $message = new \IGtListMessage();
        $message->set_isOffline(false);//是否离线
//        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        $contentId = $igt->getContentId($message);

        $targetList = [];
        foreach ($CIDS as $key=> $item){
            $target = new \IGtTarget();
            $target->set_appId($Config['AppID']);
            $target->set_clientId($item);
            $targetList[] =$target;
        }
        // 发送给目标人群
        return $igt->pushMessageToList($contentId, $targetList);
    }
    // 所有
    public function AppMessageParcel($template){

        $Config = plugin_config('GeTui');

        $igt = new \IGeTui($this->HOST, $Config['AppKey'], $Config['MasterSecret']);
        //个推信息体
        //基于应用消息体
        $message = new \IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);
        $appIdList = array($Config['AppID']);
        $message->set_appIdList($appIdList);
        return $igt->pushMessageToApp($message);
    }


    // 透传消息
    public function IGtTransmissionTemplateDemo($json)
    {
        $Config = plugin_config('GeTui');
        $template = new \IGtTransmissionTemplate();
        $template->set_appId($Config['AppID']);//应用appid
        $template->set_appkey($Config['AppKey']);//应用appkey
        $template->set_transmissionType(2);//透传消息类型
        $template->set_transmissionContent(json_encode($json));//透传内容
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        //APN简单推送
//        $template = new IGtAPNTemplate();
//        $apn = new IGtAPNPayload();
//        $alertmsg=new SimpleAlertMsg();
//        $alertmsg->alertMsg="";
//        $apn->alertMsg=$alertmsg;
////        $apn->badge=2;
////        $apn->sound="";
//        $apn->add_customMsg("payload","payload");
//        $apn->contentAvailable=1;
//        $apn->category="ACTIONABLE";
//        $template->set_apnInfo($apn);
//        $message = new IGtSingleMessage();

        //APN高级推送
        $apn = new \IGtAPNPayload();
        $alertmsg = new \DictionaryAlertMsg();
        $alertmsg->body = "body";
        $alertmsg->actionLocKey = "ActionLockey";
        $alertmsg->locKey = "LocKey";
        $alertmsg->locArgs = array("locargs");
        $alertmsg->launchImage = "launchimage";
//        IOS8.2 支持
        $alertmsg->title = "Title";
        $alertmsg->titleLocKey = "TitleLocKey";
        $alertmsg->titleLocArgs = array("TitleLocArg");

        $apn->alertMsg = $alertmsg;
        $apn->badge = 7;
        $apn->sound = "";
        $apn->add_customMsg("payload", "payload");
        $apn->contentAvailable = 1;
        $apn->category = "ACTIONABLE";
        $template->set_apnInfo($apn);

        //PushApn老方式传参
//    $template = new IGtAPNTemplate();
//          $template->set_pushInfo("", 10, "", "com.gexin.ios.silence", "", "", "", "");

        return $template;
    }




    public function IGtNotificationTemplateDemo($title, $content, $json)
    {
        $Config = plugin_config('GeTui');

        $template = new \IGtNotificationTemplate();
        $template->set_appId($Config['AppID']);//应用appid
        $template->set_appkey($Config['AppKey']);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent(json_encode($json));//透传内容
        $template->set_title($title);//通知栏标题
        $template->set_text($content);//通知栏内容
//        $template->set_logo("http://wwww.igetui.com/logo.png");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }

    public function install(){
        return true;
    }


    public function uninstall(){
        return true;
    }
}
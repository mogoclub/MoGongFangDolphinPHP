<?php

namespace plugins\GeTui\Controller;
use app\common\controller\Common;

require_once(ROOT_PATH . 'plugins/GeTui/SDK/' . 'IGt.Push.php');
require_once(ROOT_PATH . 'plugins/GeTui/SDK/' . 'igetui/utils/AppConditions.php');

//define('HOST', 'http://sdk.open.api.igexin.com/apiex.htm');

class GeTui extends Common
{
    public function __construct()
    {
        $Config = plugin_config('GeTui');
        if ($Config['AppID'] == '') {
            exception('AppID must set', 500);
        } elseif ($Config['AppSecret'] == '') {
            exception('AppSecret must set', 500);
        } elseif ($Config['AppKey'] == '') {
            exception('AppKey must set', 500);
        } elseif ($Config['MasterSecret'] == '') {
            exception('MasterSecret must set', 500);
        }
    }




    // 透传列表 透传所有 消息列表 消息所有

//    public function


    public function pushMessageToList(){
        $Config = plugin_config('GeTui');
//        putenv("gexin_pushList_needDetails=true");
        $igt = new \IGeTui(HOST,$Config['AppKey'],$Config['MasterSecret']);
        //$igt = new IGeTui('',APPKEY,MASTERSECRET);//此方式可通过获取服务端地址列表判断最快域名后进行消息推送，每10分钟检查一次最快域名
        //消息模版：
        // LinkTemplate:通知打开链接功能模板
//        $template = $this->IGtLinkTemplateDemo();

        $template = $this->IGtNotificationTemplateDemo('我是一条指定的消息','aaaa',[]);
//        $template = $this->IGtTransmissionTemplateDemo(['a'=>'heihei']);
        //定义"ListMessage"信息体
        $message = new \IGtListMessage();
        $message->set_isOffline(false);//是否离线
//        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        $contentId = $igt->getContentId($message);
        //接收方1
        $target1 = new \IGtTarget();
        $target1->set_appId($Config['AppID']);
        $target1->set_clientId('ce2913e4fab294786863f04e8634abf9');
        //$target1->set_alias(Alias1);

        $targetList[0] = $target1;

        $rep = $igt->pushMessageToList($contentId, $targetList);
        var_dump($rep);
        echo ("<br><br>");
    }



    public function notification($CID = [], $title, $content, $json = [])
    {
//        halt(config());
        if (!is_array($CID)) exception('CID must be a array');
        if ($title == '' || $content == '') exception('缺少参数');

//        if()
        $Config = plugin_config('GeTui');


        $igt = new \IGeTui(HOST, $Config['AppKey'], $Config['MasterSecret']);
        $template = $this->IGtNotificationTemplateDemo($title, $content, $json);
        //个推信息体
        //基于应用消息体
        $message = new \IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);

        $appIdList = array($Config['AppID']);
        $phoneTypeList = array('ANDROID');
        $provinceList = array('浙江');
        $tagList = array('haha');

//        $cdt = new \AppConditions();
//        $cdt->addCondition(AppConditions::PHONE_TYPE, $phoneTypeList);
//        $cdt->addCondition(AppConditions::REGION, $provinceList);
//        $cdt->addCondition(AppConditions::TAG, $tagList);

        $message->set_appIdList($appIdList);


        $contentId = $igt->getContentId($message);

//        $message->set_conditions($cdt);
        $targetList =[];
//        if(count($CID)!=0){
//
//
//            foreach ($CID as $item){
//                $target = new \IGtTarget();
//                $target->set_appId($Config['AppID']);
//                $target->set_clientId($item);
//                array_push($targetList,$target);
//            }
//
//
//            $rep = $igt->pushMessageToList($contentId, $targetList);
//            halt($rep);
//            $rep = $igt->pushMessageToApp($message);
//        }else{
//            $rep = $igt->pushMessageToApp($message);
//        }

        $rep = $igt->pushMessageToApp($message);


       dump($rep);
    }

//    public function transmission

    public function parcel($template,$CIDS= false){
        $Config = plugin_config('GeTui');

        $igt = new \IGeTui(HOST, $Config['AppID'], $Config['MasterSecret']);
        //个推信息体
        //基于应用消息体
        $message = new \IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);
        $appIdList = array($Config['AppID']);
        $message->set_appIdList($appIdList);

        // 判断是否是全部

        $rep = $igt->pushMessageToApp($message);

        return $rep;
    }

    public function transmission($json = [])
    {

        $template = $this->IGtTransmissionTemplateDemo($json);

        $this->parcel($template);

        die;

        $Config = plugin_config('GeTui');

        $igt = new \IGeTui(HOST, $Config['AppID'], $Config['MasterSecret']);
        $template = $this->IGtTransmissionTemplateDemo($json);
        //个推信息体
        //基于应用消息体
        $message = new \IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);
        $appIdList = array($Config['AppID']);
        $message->set_appIdList($appIdList);
//        $message->set_conditions($cdt);
        $rep = $igt->pushMessageToApp($message);

        return $rep;
    }

    public function IGtLinkTemplateDemo()
    {
        $Config = plugin_config('GeTui');
        $template = new \IGtLinkTemplate();
        $template->set_appId($Config['AppID']);//应用appid
        $template->set_appkey($Config['AppKey']);//应用appkey
        $template->set_title("请输入通知标题");//通知栏标题
        $template->set_text("请输入通知内容");//通知栏内容
        $template->set_logo("");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        $template->set_url("http://www.getui.com/");//打开连接地址
        //$template->set_notifyStyle(0);
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        //iOS推送需要设置的pushInfo字段
//        $apn = new IGtAPNPayload();
//        $apn->alertMsg = "alertMsg";
//        $apn->badge = 11;
//        $apn->actionLocKey = "启动";
//    //        $apn->category = "ACTIONABLE";
//    //        $apn->contentAvailable = 1;
//        $apn->locKey = "请输入通知栏内容";
//        $apn->title = "请输入通知栏标题";
//        $apn->titleLocArgs = array("titleLocArgs");
//        $apn->titleLocKey = "请输入通知栏标题";
//        $apn->body = "body";
//        $apn->customMsg = array("payload"=>"payload");
//        $apn->launchImage = "launchImage";
//        $apn->locArgs = array("locArgs");
//
//        $apn->sound=("test1.wav");;
//        $template->set_apnInfo($apn);
        return $template;
    }

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

}
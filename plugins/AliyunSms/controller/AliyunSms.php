<?php
namespace plugins\AliyunSms\controller;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use app\common\controller\Common;
use think\Db;

/**
 * User jingkewen
 * Class WeChat 微信SDK操作接口类
 * @package plugins\AliyunSms\controller
 */
class AliyunSms extends Common {
    //阿里短信接口
    public function sendSms($phoneNumbers,$templateParam = null,$outId = null,$smsUpExtendCode = null){
        // 读取微信插件配置
        $Config = plugin_config('AliyunSms');
        // 插件启用状态检测
        $Status = static::getPluginStatus();
        if (!$Status) $this->error('阿里短信插件未开启或未安装!');
        // 检测插件必填项
        if (!$Config['AccessKeyID'] || !$Config['AccessKeySecret'] || !$Config['SignName'] || !$Config['TemplateCode']) {
            $this->error('短信AccessKeyID/AccessKeySecret/SignName/TemplateCode配置不完整!');
        }
        return static::do_get_sms($Config['AccessKeyID'],$Config['AccessKeySecret'],$Config['SignName'],$Config['TemplateCode'],$phoneNumbers,$templateParam,$outId,$smsUpExtendCode);
    }
    /**
     * 检测插件是否安装和开启
     * @return bool
     */
    private static function getPluginStatus() {
        $pluginRecord = Db::name('admin_plugin')->where('identifier', '=', 'aliyunsms.jing.plugin')->find();
        if (is_null($pluginRecord)) {
            return false;
        } else {
            // 检测插件是否开启
            $Status = $pluginRecord['status'];
            return $Status != 1 ? false : true;
        }
    }
    public static function do_get_sms($accessKeyId,$accessKeySecret,$signName, $templateCode, $phoneNumbers, $templateParam = null, $outId = null, $smsUpExtendCode = null){
        require_once dirname(dirname(__FILE__)) . '/dysms/vendor/autoload.php';
        Config::load();             //加载区域结点配置
        //短信API产品名（短信产品名固定，无需修改）
        $product = "Dysmsapi";
        //短信API产品域名（接口地址固定，无需修改）
        $domain = "dysmsapi.aliyuncs.com";
        //暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
        $region = "cn-hangzhou";
        // 初始化用户Profile实例
        $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
        // 增加服务结点
        DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        // 初始化AcsClient用于发起请求
        $acsClient= new DefaultAcsClient($profile);
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();
        // 必填，设置雉短信接收号码
        $request->setPhoneNumbers($phoneNumbers);
        // 必填，设置签名名称
        $request->setSignName($signName);
        // 必填，设置模板CODE
        $request->setTemplateCode($templateCode);
        // 可选，设置模板参数
        if($templateParam) {
            $request->setTemplateParam(json_encode($templateParam));
        }
        //发起访问请求
        $acsResponse = $acsClient->getAcsResponse($request);
        //返回请求结果
        return json_decode(json_encode($acsResponse),true);
    }

}
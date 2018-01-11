<?php
/**
 * 得到最上级用户id
 * @param $id 需要查询的用户id
 * @return mixed
 * @author 杨希成
 */
function mogo_get_pid($id)
{
    $user = $this->get_find(['id' => $id]);
    if ($user->group_id == 2) return $user->id;
    $user_all = collection($this->get_select())->toArray();
    $pids = get_parent_id($user_all, $user->pid);
    foreach ($pids as $v) {
        if ($v['group_id'] == 2) {
            return $v['id'];
        }
    }
}
/**
 * 查询所有下级
 * @param $array      需要筛选的数组
 * @param $parent_id  要查询的id
 * @return array
 * @author 杨希成
 */
function mogo_get_children_id($array, $parent_id)
{
    $arr = array();
    foreach ($array as $v) {
        if ($v['pid'] == $parent_id) {
            $arr[] = $v;
            $arr = array_merge($arr, get_children_id($array, $v['id']));
        }
    }
    return $arr;
}
/**
 * 得到随机小数使用案例
 * @param $mix_num
 * @param $max_num
 * @return float|int
 * @author 杨希成
 */

function mogo_get_rand($mix_num,$max_num){
    $FloatLength = getFloatLength($mix_num);
    $jishu_rand = pow(10, $FloatLength);
    $shop_min = $mix_num * $jishu_rand;
    $shop_max = $max_num * $jishu_rand;
    $rand = rand($shop_min, $shop_max) / $jishu_rand;
    return $rand;
}
/**
 * //计算小数点后位数
 * @param $num
 * @return int
 * @author 杨希成
 */
function mogo_getFloatLength($num) {
    $count = 0;
    $temp = explode ( '.', $num );
    if (sizeof ( $temp ) > 1) {
        $decimal = end ( $temp );
        $count = strlen ( $decimal );
    }
    return $count;
}
/**
 * @param $num 科学计数法字符串  如 2.1E-5
 * @param int $double 小数点保留位数 默认5位
 * @return string
 * @author 杨希成
 */
function mogo_sctonum($num, $double = 5)
{
    if (false !== stripos($num, "e")) {
        $a = explode("e", strtolower($num));
        return bcmul($a[0], bcpow(10, $a[1], $double), $double);
    }
    return $num;
}
/**
 * 数字格式化
 * @author 杨希成
 */
function mogo_num_format($num){
    if(!is_numeric($num)){
        return false;
    }
    $num = explode('.',$num);//把整数和小数分开
    $rl = $num[1];//小数部分的值
    $j = strlen($num[0]) % 3;//整数有多少位
    $sl = substr($num[0], 0, $j);//前面不满三位的数取出来
    $sr = substr($num[0], $j);//后面的满三位的数取出来
    $i = 0;
    $rvalue = '';
    while($i <= strlen($sr)){
        $rvalue = @$rvalue.','.substr($sr, $i, 3);//三位三位取出再合并，按逗号隔开
        $i = $i + 3;
    }
    $rvalue = $sl.$rvalue;
    $rvalue = substr($rvalue,0,strlen($rvalue)-1);//去掉最后一个逗号
    $rvalue = explode(',',$rvalue);//分解成数组
    if($rvalue[0]==0){
        array_shift($rvalue);//如果第一个元素为0，删除第一个元素
    }
    $rv = $rvalue[0];//前面不满三位的数
    for($i = 1; $i < count($rvalue); $i++){
        $rv = $rv.','.$rvalue[$i];
    }
    if(!empty($rl)){
        $rvalue = $rv.'.'.$rl;//小数不为空，整数和小数合并
    }else{
        $rvalue = $rv;//小数为空，只有整数
    }
    return $rvalue;
}
/**
 * 限制字符长度，用...代替
 * @param $sourcestr string
 * @param $cutlength string
 * @return string
 * @author 杨希成
 */
function mogo_cut_str($sourcestr,$cutlength)
{
    $returnstr='';
    $i=0;
    $n=0;
    $str_length=strlen($sourcestr);//字符串的字节数
    while (($n<$cutlength) and ($i<=$str_length))
    {
        $temp_str=substr($sourcestr,$i,1);
        $ascnum=Ord($temp_str);//得到字符串中第$i位字符的ascii码
        if ($ascnum>=224)    //如果ASCII位高与224，
        {
            $returnstr=$returnstr.substr($sourcestr,$i,3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i=$i+3;            //实际Byte计为3
            $n++;            //字串长度计1
        }
        elseif ($ascnum>=192) //如果ASCII位高与192，
        {
            $returnstr=$returnstr.substr($sourcestr,$i,2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i=$i+2;            //实际Byte计为2
            $n++;            //字串长度计1
        }
        elseif ($ascnum>=65 && $ascnum<=90) //如果是大写字母，
        {
            $returnstr=$returnstr.substr($sourcestr,$i,1);
            $i=$i+1;            //实际的Byte数仍计1个
            $n++;            //但考虑整体美观，大写字母计成一个高位字符
        }
        else                //其他情况下，包括小写字母和半角标点符号，
        {
            $returnstr=$returnstr.substr($sourcestr,$i,1);
            $i=$i+1;            //实际的Byte数计1个
            $n=$n+0.5;        //小写字母和半角标点等与半个高位字符宽...
        }
    }
    if ($str_length>$i){
        $returnstr = $returnstr . "...";//超过长度时在尾处加上省略号
    }
    return $returnstr;
}
/**
 * 得到发布时间差
 * @param $time
 * @return string
 * @author 杨希成
 */
function mogo_get_time($time){
    $now=date('Y-m-d H:i:s');
    $date=floor((strtotime($now)-strtotime($time))/86400);                  //天
    $hour=floor((strtotime($now)-strtotime($time))%86400/3600);             //时
    $minute=floor((strtotime($now)-strtotime($time))%86400/60);             //分
    $second=floor((strtotime($now)-strtotime($time))%86400%60);             //秒
    if($date){
        return $date.'天前';
    }else if($hour){
        return $hour.'小时前';
    }else if($minute){
        return $minute.'分钟前';
    }else{
        return '刚刚';
    }
}


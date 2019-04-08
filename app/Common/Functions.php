<?php

/** 
 * 密码加密
 */
function encodePassword($pwd = '')
{
    $password = md5(md5($pwd)."dsagds2342343gidsagds");
    return substr($password,0,strlen($password)-2);
}

/**
 * 参数序列化，去标签
 */
function delHtmlTag($request,$arr = [])
{
    foreach($request->all() as $key => $item){
        if(!empty($item) && is_string($item)){
            if(!in_array($key,$arr)){
                $request->offsetSet($key, htmlspecialchars(strip_tags($item)));
            }
        }
    }
}

function vulNumber($vid , $time)
{
    if(empty($time)){
        $year = date('Y' , time());
    }else{
        $year = date('Y' , strtotime($time));
    }
    if(empty($vid)){
        return null;
    }
    $num = $vid*2+15;
    $num =  str_pad($num, 5, "0", STR_PAD_LEFT); 
    return 'com-'.$year.'-'.$num;
}

function emailUrl($email){
    $t = explode('@', $email)[1];
    if($t == '163.com') {
        return 'mail.163.com';
    }else if($t == 'vip.163.com') {
        return 'vip.163.com';
    }else if($t == '126.com') {
        return 'mail.126.com';
    }else if($t == 'qq.com' || $t == 'vip.qq.com' || $t == 'foxmail.com') {
        return 'mail.qq.com';
    }else if($t == 'gmail.com') {
        return 'mail.google.com';
    }else if($t == 'sohu.com') {
        return 'mail.sohu.com';
    }else if($t == 'tom.com') {
        return 'mail.tom.com';
    }else if($t == 'vip.sina.com') {
        return 'vip.sina.com';
    }else if($t == 'sina.com.cn' || $t == 'sina.com') {
        return 'mail.sina.com.cn';
    }else if($t == 'tom.com') {
        return 'mail.tom.com';
    }else if($t == 'yahoo.com.cn' || $t == 'yahoo.cn') {
        return 'mail.cn.yahoo.com';
    }else if($t == 'tom.com') {
        return 'mail.tom.com';
    }else if($t == 'yeah.net') {
        return 'www.yeah.net';
    }else if($t == '21cn.com') {
        return 'mail.21cn.com';
    }else if($t == 'hotmail.com') {
        return 'www.hotmail.com';
    }else if($t == 'sogou.com') {
        return 'mail.sogou.com';
    }else if($t == '188.com') {
        return 'www.188.com';
    }else if($t == '139.com') {
        return 'mail.10086.cn';
    }else if($t == '189.cn') {
        return 'webmail15.189.cn/webmail';
    }else if($t == 'wo.com.cn') {
        return 'mail.wo.com.cn/smsmail';
    }else if($t == '139.com') {
        return 'mail.10086.cn';
    }else{
        return '';
    }
}

function numberVulId($nums)
{
	$comInfo = explode("-",$nums);
	if(!empty($comInfo[2])){
        $id = (int) $comInfo[2];
        $id = ($id-15)/2;
        if($id <= 0){
            $id = '';
        }
    }else{
    	$id = '';
    }
	return $id;
}

function Ethprice($price)
{
    
    $price = $price/1000000000000000000;
    
    $price_str = (string)$price;
    $e_array = explode('E-', $price_str);
    if(count($e_array) > 1){
        $e_start_length = strlen($e_array[0]);
        //var_dump($e_start_length);
        $e_length =  $e_array[1];
        $e_end_lenth = $e_length+$e_start_length-2;
        $s_price = (string)number_format($price,$e_end_lenth);
    }else{
        $s_price =  (string)$price;
    }
        
    $e_t_array = explode('.', $s_price);
    if(count($e_t_array) > 1){
        $end_s_sprice = substr($s_price, 0,strlen($e_t_array[0]) + 9);
        return $end_s_sprice;
    
    }else{
        return $s_price;
    }
    return $price;
}

function diffTime($startdate)
{
    $date1 = date('Y-m-d H:i:s',time());
    $date2 = date('Y-m-d H:i:s',$startdate);
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    $time['year'] = $interval->format('%Y');
    $time['month'] = $interval->format('%m');
    $time['day'] = $interval->format('%d');
    $time['hour'] = $interval->format('%H');
    $time['min'] = $interval->format('%i');
    $time['sec'] = $interval->format('%s');
    $time['days'] = $interval->format('%a'); // 两个时间相差总天数
    
    $day = $time['day'];
    if($day== 0){
        $day_str = "";
    }else if($day==1){
        $day_str = " $day day";
    }else{
        $day_str = " $day days";
    }
    //hour
    $hour = $time['hour'];
    if($hour == 0){
        $hour_str = "";
    }else if($hour==1){
        $hour_str = " $hour hr";
    }else{
        $hour_str = " $hour hrs";
    }
    
    $minute = $time['min'];
    if($minute == 0){
        $minute_str = "";
    }else if($minute==1){
        $minute_str = " $minute min";
    }else{
        $minute_str = " $minute mins";
    }
    if(!empty($day_str)){
        if(!empty($hour_str)){
            $return = $day_str.$hour_str;
        }else{
            $return = $day_str.$minute_str;
        }
    }else{
        $return = $hour_str.$minute_str;
    }
    return $return." ago";
}

function score($vul = [], $fix = [])
{
    $serious_per_score = 10;
    $high_per_score    = 5;
    $mid_per_score     = 3;
    $low_per_score     = 1;

	$serious_max_score = 10;
	$high_max_score    = 60;
	$mid_max_score     = 80;
	$low_max_score     = 90;
	
	$serious_min_score = 0;
	$high_min_score    = 10;
	$mid_min_score     = 60;
	$low_min_score     = 80;
    
    $serious = $vul['serious'];
    $high = $vul['high'];
    $mid = $vul['mid'];
    $low = $vul['low'];
    
    $serious = (int)$serious;
    $high = (int)$high;
    $mid = (int)$mid;
    $low = (int)$low;
    $fix = (int)$fix;
    //初始份数
    $score = 100;
    //最高等级最高分数
    if($serious > 0){
        $score = $serious_max_score;
    }else if($high > 0){
        $score = $high_max_score;
    }else if($mid > 0){
        $score = $mid_max_score;
    }else if($low > 0){
        $score = $low_max_score;
    }
    //逐级减分
    if($serious > 0){
        $score = $score-$serious*$serious_per_score;
    }
    if($high > 0){
        $score = $score-$high*$high_per_score;
    }
    if($mid > 0){
        $score = $score-$mid*$mid_per_score;
    }
    if($low > 0){
        $score = $score-$low*$low_per_score;
    }
    //小于0赋予0
    if($score < 0){
        $score = 0;
    }
    //最高等级最低分
    if($serious > 0){
        if($score < $serious_min_score){
            $score = $serious_min_score;
        }
    }else if($high > 0){
        if($score < $high_min_score){
            $score = $high_min_score;
        }
    }else if($mid > 0){
        if($score < $mid_min_score){
            $score = $mid_min_score;
        }
    }else if($low > 0){
        if($score < $low_min_score){
            $score = $low_min_score;
        }
    }
    return $score;
}

function urlGet($url) {
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
    // curl_setopt($ch, CURLOPT_HEADER, 0);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec ( $ch );
    curl_close ( $ch );
    return $response;
}

function timeToFormt($time = 0, $type = 1)
{
    $d = floor($time / (3600*24)) ?? 0;
    $h = floor(($time % (3600*24)) / 3600) ?? 0;
    $m = floor((($time % (3600*24)) % 3600) / 60) ?? 0;
    switch ($type) {
        case 1:
            $formt = $d.'天'.$h.'小时'.$m.'分';
            break;
        
        case 2:
            $formt = $d.'天'.$h.'小时';
            break;
        
        case 3:
            $formt = $h.'小时'.$m.'分';
            break;

        case 4:
            $formt = $m.'分';
            break;

        default:
            $formt = $d.'天'.$h.'小时'.$m.'分';
            break;
    }

    return $formt;
}

/**
 * 截取HTML不断开
 *
 * @param [type] $str
 * @param [type] $lenth
 * @param string $replace
 * @param string $anchor
 * @return void
 */
function cut_html_str($str, $lenth, $replace='', $anchor='<!-- break -->'){ 
    $_lenth = mb_strlen($str, "utf-8"); // 统计字符串长度（中、英文都算一个字符）
    if($_lenth <= $lenth){
        return $str;    // 传入的字符串长度小于截取长度，原样返回
    }
    $strlen_var = strlen($str);     // 统计字符串长度（UTF8编码下-中文算3个字符，英文算一个字符）
    if(strpos($str, '<') === false){ 
        return mb_substr($str, 0, $lenth);  // 不包含 html 标签 ，直接截取
    } 
    if($e = strpos($str, $anchor)){ 
        return mb_substr($str, 0, $e);  // 包含截断标志，优先
    } 
    $html_tag = 0;  // html 代码标记 
    $result = '';   // 摘要字符串
    $html_array = array('left' => array(), 'right' => array()); //记录截取后字符串内出现的 html 标签，开始=>left,结束=>right
    /*
    * 如字符串为：<h3><p><b>a</b></h3>，假设p未闭合，数组则为：array('left'=>array('h3','p','b'), 'right'=>'b','h3');
    * 仅补全 html 标签，<? <% 等其它语言标记，会产生不可预知结果
    */
    for($i = 0; $i < $strlen_var; ++$i) { 
        if(!$lenth) break;  // 遍历完之后跳出
        $current_var = substr($str, $i, 1); // 当前字符
        if($current_var == '<'){ // html 代码开始 
            $html_tag = 1; 
            $html_array_str = ''; 
        }else if($html_tag == 1){ // 一段 html 代码结束 
            if($current_var == '>'){ 
                $html_array_str = trim($html_array_str); //去除首尾空格，如 <br / > < img src="" / > 等可能出现首尾空格
                if(substr($html_array_str, -1) != '/'){ //判断最后一个字符是否为 /，若是，则标签已闭合，不记录
                    // 判断第一个字符是否 /，若是，则放在 right 单元 
                    $f = substr($html_array_str, 0, 1); 
                    if($f == '/'){ 
                        $html_array['right'][] = str_replace('/', '', $html_array_str); // 去掉 '/' 
                    }else if($f != '?'){ // 若是?，则为 PHP 代码，跳过
                        // 若有半角空格，以空格分割，第一个单元为 html 标签。如：<h2 class="a"> <p class="a"> 
                        if(strpos($html_array_str, ' ') !== false){ 
                        // 分割成2个单元，可能有多个空格，如：<h2 class="" id=""> 
                        $html_array['left'][] = strtolower(current(explode(' ', $html_array_str, 2))); 
                        }else{ 
                        //若没有空格，整个字符串为 html 标签，如：<b> <p> 等，统一转换为小写
                        $html_array['left'][] = strtolower($html_array_str); 
                        } 
                    } 
                } 
                $html_array_str = ''; // 字符串重置
                $html_tag = 0; 
            }else{ 
                $html_array_str .= $current_var; //将< >之间的字符组成一个字符串,用于提取 html 标签
            } 
        }else{ 
            --$lenth; // 非 html 代码才记数
        } 
        $ord_var_c = ord($str{$i}); 
        switch (true) { 
            case (($ord_var_c & 0xE0) == 0xC0): // 2 字节 
                $result .= substr($str, $i, 2); 
                $i += 1; break; 
            case (($ord_var_c & 0xF0) == 0xE0): // 3 字节
                $result .= substr($str, $i, 3); 
                $i += 2; break; 
            case (($ord_var_c & 0xF8) == 0xF0): // 4 字节
                $result .= substr($str, $i, 4); 
                $i += 3; break; 
            case (($ord_var_c & 0xFC) == 0xF8): // 5 字节 
                $result .= substr($str, $i, 5); 
                $i += 4; break; 
            case (($ord_var_c & 0xFE) == 0xFC): // 6 字节
                $result .= substr($str, $i, 6); 
                $i += 5; break; 
            default: // 1 字节 
                $result .= $current_var; 
        } 
    } 
    if($html_array['left']){ //比对左右 html 标签，不足则补全
        $html_array['left'] = array_reverse($html_array['left']); //翻转left数组，补充的顺序应与 html 出现的顺序相反
        foreach($html_array['left'] as $index => $tag){ 
            $key = array_search($tag, $html_array['right']); // 判断该标签是否出现在 right 中
            if($key !== false){ // 出现，从 right 中删除该单元
                unset($html_array['right'][$key]); 
            }else{ // 没有出现，需要补全 
                $result .= '</'.$tag.'>'; 
            } 
        } 
    } 
    return $result.$replace; 
}

function getTitle($info, $language = 'zh_CN')
{
    if($language == "en_US"){
        if(empty($info->title_en)){
            if(!empty($info->title_zh)){
                $title = $info->title_zh;
            }else{
                if(!empty($info->com_title)){
                    $title = $info->com_title;
                }else{
                    $title = $info->title;
                }
            }
        }else{
            $title = $info->title_en;
        }
        
    }else{
        if(empty($info->title_zh)){
            if(!empty($info->title_en)){
                $title = $info->title_en;
            }else{
                if(!empty($info->com_title)){
                    $title = $info->com_title;
                }else{
                    $title = $info->title;
                }
            }
        }else{
            $title = $info->title_zh;
        }			
        
    }
    
    return $title;
}
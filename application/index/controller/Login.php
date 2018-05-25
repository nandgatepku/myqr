<?php
/**
 * Created by PhpStorm.
 * User: PTcZn
 * Date: 2018/5/25
 * Time: 13:06
 */

namespace app\index\controller;


use app\index\common\Base;
use think\Db;

class Login extends Base
{
    public function login(){
        $user_agent = $this->get_http_user_agent();
        $ip = $this->get_ip();
        $browse_info = $this->get_browse_info();
        $os = $this->get_os();
        $now_time = date('Y-m-d H:i:s', time());

        $user =[
            'ip' => $ip,
            'browse_info' => $browse_info,
            'os' => $os,
            'login_time' => $now_time,
        ];
        $user_json = json_encode($user);
        $md5_user = md5($user_json);

        $insert['user_agent'] = $user_agent;
        $insert['ip'] = $ip;
        $insert['browse_info'] = $browse_info;
        $insert['os'] = $os;
        $insert['login_time'] = $now_time;
        $insert['md5'] = $md5_user;

        $db_insert = Db::table('web_login')->insert($insert);
        $qr_data = action('Wechat/get_qrcode',['md5_user'=>$md5_user]);
        $ok = file_put_contents('static/qrcode/'.$md5_user.'.jpg', $qr_data);

        $this->assign('md5_user',$md5_user);
        return $this->fetch('login');

//        return $this->fetch('login');
    }

    function get_http_user_agent(){
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        return $user_agent;
    }

    function get_ip(){
        if ($_SERVER['REMOTE_ADDR']) {//判断SERVER里面有没有ip，因为用户访问的时候会自动给你网这里面存入一个ip
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {//如果没有去系统变量里面取一次 getenv()取系统变量的方法名字
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {//如果还没有在去系统变量里取下客户端的ip
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }

    function get_browse_info() {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $br = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/i', $br)) {
                $br = 'MSIE';
            } else if (preg_match('/Firefox/i', $br)) {
                $br = 'Firefox';
            } else if (preg_match('/Chrome/i', $br)) {
                $br = 'Chrome';
            } else if (preg_match('/Safari/i', $br)) {
                $br = 'Safari';
            } else if (preg_match('/Opera/i', $br)) {
                $br = 'Opera';
            } else {
                $br = 'Other';
            }
            return $br;
        } else {
            return 'unknow';
        }
    }

    function get_os() {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $os = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/win/i', $os)) {
                $os = 'Windows';
            } else if (preg_match('/mac/i', $os)) {
                $os = 'MAC';
            } else if (preg_match('/linux/i', $os)) {
                $os = 'Linux';
            } else if (preg_match('/unix/i', $os)) {
                $os = 'Unix';
            } else if (preg_match('/bsd/i', $os)) {
                $os = 'BSD';
            } else {
                $os = 'Other';
            }
            return $os;
        } else {
            return 'unknow';
        }
    }

    public function look_scan(){
        $md5_qr = $_POST['md5_qr'];
//        $md5_qr  = '88f4d6a5b2ac98b700fa06f1b579b61a';
        $where['md5_qr'] = $md5_qr;
        $scan = Db::table('wx_login')->where($where)->field('nickName,login_time')->select();
//        return json($scan);
        return json($scan);
    }



}
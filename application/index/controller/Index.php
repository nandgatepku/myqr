<?php
namespace app\index\controller;

use app\index\common\Base;
use app\index\api\wxBizDataCrypt;
use app\index\api\errorCode;
use think\Db;

class Index extends Base
{
    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';
    }

    public function wxLogin() {
        $code = input("code", '', 'htmlspecialchars_decode');
        $rawData = input("rawData", '', 'htmlspecialchars_decode');
        $signature = input("signature", '', 'htmlspecialchars_decode');
        $encryptedData = input("encryptedData", '', 'htmlspecialchars_decode');
        $iv = input("iv", '', 'htmlspecialchars_decode');

        $APPID = 'wx9babc5f031633181';
        $AppSecret = '63e60bd76092dcdb2a0867ba6eea52b5';
        $wx_request_url = 'https://api.weixin.qq.com/sns/jscode2session';
        $params = [
            'appid' => $APPID,
            'secret' => $AppSecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $res = makeRequest($wx_request_url, $params);

        if ($res['code'] !== 200 || !isset($res['result']) || !isset($res['result'])) {
            return json(ret_message('requestTokenFailed'));
//            return $res['result'];
        }
        $reqData = json_decode($res['result'], true);
        if (!isset($reqData['session_key'])) {
            return json(ret_message('requestTokenFailed'));
//            return $res['result'];
        }
        $sessionKey = $reqData['session_key'];

        $signature2 = sha1($rawData . $sessionKey);

        if ($signature2 !== $signature) return ret_message("signNotMatch");

        $pc = new WXBizDataCrypt($APPID, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        if ($errCode !== 0) {
            return json(ret_message("encryptDataNotMatch"));
//            print_r($errCode);
        }

        $wr = json_decode($data);
        $insert['openId'] = $wr -> openId;
        $insert['nickName'] = $wr -> nickName;
        $insert['gender'] = $wr -> gender;
        $insert['language'] = $wr -> language;
        $insert['city'] = $wr -> city;
        $insert['province'] = $wr -> province;
        $insert['country'] = $wr -> country;
        $insert['avatarUrl'] = $wr -> avatarUrl;
        $insert['login_time'] = time();

        Db::table('login')->insert($insert);

//	return json(ret_message("here"));

//        $data = json_decode($data, true);
//        $session3rd = randomFromDev(16);

//        $data['session3rd'] = $session3rd;
//        cache($session3rd, $data['openId'] . $sessionKey);

        return json($data);
    }

}

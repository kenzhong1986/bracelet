<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: shj
// +----------------------------------------------------------------------

namespace Home\Controller;

use OT\DataDictionary;
use Think\Upload;
use Think\Log;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use \Think\Exception;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class ApiController extends HomeController
{
    private $user;





//    public function __construct()
//    {
//
//        $user = (object) ['name' => 'testing', 'email' => 'testing@abc.com'];
//        $tmp=(new \Lcobucci\JWT\Builder())->setId(1);
//        $token = (new \Lcobucci\JWT\Builder())->setId(1)
//            ->setAudience('http://baidu.abc.com')
//            ->setIssuer('http://api.abc.com')
//            //->setExpiration(self::CURRENT_TIME + 3000)
//            ->set('user', $user)
//            ->getToken();
//
//
//       // echo $token;
//
//        $tokenString="eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJqdGkiOiIxIiwiYXVkIjoiaHR0cDpcL1wvY2xpZW50LmFiYy5jb20iLCJpc3MiOiJodHRwOlwvXC9hcGkuYWJjLmNvbSIsInVzZXIiOnsibmFtZSI6InRlc3RpbmciLCJlbWFpbCI6InRlc3RpbmdAYWJjLmNvbSJ9fQ.";
//
//
//
//      $token=   (new \Lcobucci\JWT\Parser())->parse($tokenString); // Parses from a string
//        $token->getHeaders(); // Retrieves the token header
//        $token->getClaims(); // Retrieves the token claims
//
//        echo $token->getHeader('jti'); // will print "4f1g23a12aa"
//        echo $token->getClaim('iss'); // will print "http://example.com"
//        echo $token->getClaim('aud')[0]; // will print "http://example.org"
//        echo $token->getClaim('uid'); // will print "1"
//
//
//        die;
//
//      $uid=session("uid");
//        if($uid){
//
//        }
//
//        /* $request = $this->slim->request()->post();
//        $userid = isset($request['user_id'])?$request['user_id']:'';
//        if(!empty($userid))
//        {
//            if(is_numeric($userid)){
//                $this->user = $this->em->getRepository('\Entities\ShopUser')->getOneUserById($userid);
//            }
//            else
//            {
//                $this->user = $this->em->getRepository('\Entities\ShopUser')->getOneUserByUserId($userid);
//            }
//            $this->neigou = \Jiayou\UserCommon::SetEventNeigou($this->user);
//        } */
//    }
    public function _initialize(){
        //判断用户是否已经登录
          parent::_initialize();

    }

    //系统首页
    public function index()
    {

        $sign = D('Sign');
        /* 	$data['uid']=2;
            $data['sign_time']=time();
            $data['point']=1; */
//     	$sign->creat();
//     	$r=$sign->add($data);
        $sign->create();
        if ($sign->create()) {
            $dd = $sign->add();
        }
        var_dump($dd);
        die;
        $r = $sign->getTodaySign(1);
        echo $sign->getLastSql();
        var_dump($r);
        die;
        /*  $category = D('Category')->getTree();
         $lists    = D('Document')->lists(null);

         $this->assign('category',$category);//栏目
         $this->assign('lists',$lists);//列表
         $this->assign('page',D('Document')->page);//分页


         $this->display(); */
    }

    /**
     * 检查是否已签到
     * @author shj
     * @param $uid
     * @return bool
     */
    public function is_sign($userid)
    {
//     	echo $userid;die;
        $sign = D('Sign');
        $r = $sign->getTodaySign($userid);
        /* echo $sign->getLastSql();
        var_dump($r);die; */

        if ($r) {
            $this->ajaxReturn(array("type" => "error", "msg" => "此用户今天已经签到"), 'JSON');
            return;;
        }
        $this->ajaxReturn(array("type" => "success", "msg" => "此用户今天还没签到"), 'JSON');
        return;
    }


    /**
     * 上传头像
     * @author shj
     * @param $uid
     * @return bool
     */
    public function uploadedImage()
    {
        $return = array('type' => 'success', 'msg' => '上传成功', 'data' => array());

        $config = array('maxSize' => 5 * 2014 * 1024,
            'exts' => array('jpg', 'jpeg', 'gif', 'png'),
            'savePath' => '/Avatar/',
            'autoSub' => false,
            'hash' => true,
            'saveName' => array('uniqid', '')
        );

        $upload = new Upload($config);
        $info = $upload->upload();

        //$return  = array('type' => 'success', 'msg' => '上传成功', 'data' => '123');
        if ($info) {
            $return[data] = array('path' => '/Uploads/Avatar/' . $info[file][savename]);
        } else {
            $return['type'] = 'error';
            $return['msg'] = '上传失败,原因为:' . $upload->getError();

        }


        /* 返回JSON数据 */
        $this->ajaxReturn($return, 'JSON');
//

        return;
    }


    /**
     * 获取签到题ID
     * @author shj
     * @param
     * @return  array
     */
    public function getEids()
    {
        $exercises = D('Exercises');
        $lists = $exercises->getEids();
        // var_dump($lists);die;
// print_r($lists);die;

        $this->ajaxReturn(array("type" => "success", "msg" => "以获取习题ID", "data" => $lists), 'JSON');
        return;
    }


    /**
     * 签到题
     * @author shj
     * @param $exercises_id  题id
     * @return  array
     */
    public function exercises($exercises_id)
    {
        $exercises = D('Exercises');
        $lists = $exercises->getexercises($exercises_id);
        //var_dump($lists);die;
        // print_r($lists);die;
        if (!$lists) {
            $this->ajaxReturn(array("type" => "error", "msg" => "没有获取到指定id习题"), 'JSON');
            return;
        }
        $this->ajaxReturn(array("type" => "success", "msg" => "以获取习题", "data" => $lists), 'JSON');
        return;
    }


    /**
     * 签到答题
     * @author shj
     * @param $uid
     * @return int/bool/object/array
     */
    public function sign()
    {
        //var_dump($_POST);die;
        $userid = isset($_POST['userid']) ? $_POST['userid'] : '';
        $eids = isset($_POST['exerids']) ? $_POST['exerids'] : '';

        if (!$userid) {
            $this->ajaxReturn(array("type" => "error", "msg" => "1没有获取到指定用户信息"), 'JSON');
            return;
        }
        if (!$eids) {
            $this->ajaxReturn(array("type" => "error", "msg" => "2系统内部错误,缺少题号"), 'JSON');
            return;
        }
        $sign = D('Sign');
        $r = $sign->getTodaySign($userid);
        $exercises_answer = D('ExercisesAnswer');

        if (!$r) {
            $eids = explode(',', $eids);
            foreach ($eids as $eid) {//在答题表中记录答题信息
                $eid_value = isset($_POST['exer_' . $eid]) ? $_POST['exer_' . $eid] : '';
                if (!$eid_value) {
                    $this->ajaxReturn(array("type" => "error", "msg" => "3请回答所有问题"), 'JSON');
                    return;
                } else {
                    $r = $exercises_answer->getExercisesAnswer($userid, $eid);
                    //var_dump($r);
                    if ($r) {//更新之前答题记录
                        $r[0]['optionid'] = $eid_value;
                        //var_dump($r[0]);die;
                        $exercises_answer->save($r[0]);
//     					echo 'update';
                    } else {//添加答题记录
                        $r['uid'] = $userid;
                        $r['eid'] = $eid;
                        $r['optionid'] = $eid_value;
                        $exercises_answer->add($r);
//     					echo 'add';
                    }
                }
            }
            //在签到表中记录签到信息
            $data['uid'] = $userid;
            $data['sign_time'] = time();
            $data['point'] = 1;
            $sign->add($data);
// 	     	$result=$sign->add($data);
            $this->ajaxReturn(array("type" => "success", "msg" => "签到成功"), 'JSON');
            return;
        } else {
            $this->ajaxReturn(array("type" => "error", "msg" => "4此用户今天已经签到"), 'JSON');
            return;
        }


    }


    /**
     * 运动数据记录接口
     * @author shj
     * @param $uid
     * @return int/bool/object/array
     */
    public function recordSport()
    {
        $userid = isset($_POST['uid']) ? $_POST['uid'] : '';
        $steps = isset($_POST['steps']) ? $_POST['steps'] : '';
        $distance = isset($_POST['distance']) ? $_POST['distance'] : '';
        $stepdetail = isset($_POST['stepdetail']) ? $_POST['stepdetail'] : '';
        $sporttime = isset($_POST['sporttime']) ? $_POST['sporttime'] : '';

        Log::write( $userid.'---'.$steps.'---'.$distance.'---'.$stepdetail .'---'.$sporttime,'WARN');

        if (!$userid || !$steps || !$distance||!$stepdetail||!$sporttime) {
            $this->ajaxReturn(array("type" => "error", "msg" => "缺少必须参数"), 'JSON');
            return;
        }
   //     Log::write( '------------->1' ,'WARN');
        $sport = D('Sport');

        //获取当天运动数据
        $r = $sport->getTodaySport($userid,$sporttime);
        //	var_dump($r[0]);die;
        if ($r) {//更新
            $r[0]['steps'] = $steps;
            $r[0]['distance'] = $distance;
            $r[0]['committime'] = time();
            $r[0]['sporttime'] =strtotime(date("Y-m-d 00:00:00", strtotime($sporttime)));
            $r[0]['stepdetail'] = $stepdetail;
            $sport->save($r[0]);
//     		echo 'update';
        } else {//添加
            $r['uid'] = $userid;
            $r['steps'] = $steps;
            $r['distance'] = $distance;
            $r['committime'] = time();
            $r['sporttime'] =strtotime(date("Y-m-d 00:00:00", strtotime($sporttime)));
         //   $r['sporttime'] = time();
            $r['stepdetail'] = $stepdetail;
            $sport->add($r);
//     		echo 'add';
        }
   //     Log::write( '------------->2' ,'WARN');
        $this->ajaxReturn(array("type" => "success", "msg" => "已记录运动数据"), 'JSON');
        return;
    }

    /**
     * 查询运动数据
     * @author shj
     * @param $uid ,$starttime $endtime
     * @return int/bool/object/array
     */

    public function getSport()
    {
        $userid = isset($_POST['uid']) ? $_POST['uid'] : '';
        $starttime = isset($_POST['starttime']) ? $_POST['starttime'] : '';
        $endtime = isset($_POST['endtime']) ? $_POST['endtime'] : '';
        $order = isset($_POST['order']) ? $_POST['order'] : '';
        if (!$userid || !$starttime || !$endtime) {
            $this->ajaxReturn(array("type" => "error", "msg" => "缺少必须参数"), 'JSON');
            return;
        }

        $sport = D('Sport');
        $stime = strtotime(date("Y-m-d 00:00:00", strtotime($starttime)));
        $etime = strtotime(date("Y-m-d 24:00:00", strtotime($endtime)));

        $condition['sporttime'] = array('between', array($stime, $etime));
        if ($order) {
            $r = $sport->where($condition)->order($order . ' desc')->select();
        } else
            $r = $sport->where($condition)->where('uid',$userid)->select();
        //echo $sport->getLastSql();die;

        $sport_data = array();

        foreach ($r as $key => $s) {
            if ($s['uid'] == $userid) {
                $tmp = $s;
                $tmp['sporttime']=date('Y-m-d 00:00:00',$s['sporttime']);
                array_push($sport_data,$tmp);
                if ($order) {
                    $sport_data['order'] = $key + 1;
                }

            }
        }

        $this->ajaxReturn(array("type" => "success", "msg" => "成功获取运动", "data" => $sport_data), 'JSON');
        return;
    }

    /**
     * 运动禅修数据接口
     * @author shj
     * @param $uid
     * @return int/bool/object/array
     */
    public function saveChanXiu()
    {
        $uid = isset($_POST['uid']) ? $_POST['uid'] : '';
        $starttime = isset($_POST['starttime']) ? $_POST['starttime'] : '';
        $endtime = isset($_POST['endtime']) ? $_POST['endtime'] : '';
        $howlong = isset($_POST['howlong']) ? $_POST['howlong'] : '';

        if (!$uid || !$starttime || !$endtime || !$howlong) {
            $this->ajaxReturn(array("type" => "error", "msg" => "缺少必须参数"), 'JSON');
            return;
        }

        //1.判断用户ID是否存在
        $user = D('Member');
        $map['uid'] = $uid;

        $r = $user->where($map)->select();

        if (!$r) {
            $this->ajaxReturn(array("type" => "error", "msg" => "用户不存在"), 'JSON');
            return;
        }

        //		$starttime=strtotime($starttime);
        //       date("Y-m-d H:i:s",1469006160);
        //$chanxiu['id']=1;
        $chanxiu['uid'] = $uid;
        $chanxiu['starttime'] = strtotime($starttime);
        $chanxiu['endtime'] = strtotime($endtime);
        $chanxiu['howlong'] = $howlong;


        $chanxiudata = D('Chanxiudata');


        $result = $chanxiudata->add($chanxiu);
        if (!$result) {
            $this->ajaxReturn(array("type" => "error", "msg" => "保存数据过程出错，请联系管理员"), 'JSON');
        }

        $this->ajaxReturn(array("type" => "success", "msg" => "已记录禅修数据"), 'JSON');
        return;
    }


    /**
     * 获取禅修数据
     */
    public function getChanxiu()
    {

        $uid = isset($_POST['uid']) ? $_POST['uid'] : '';
        $starttime = isset($_POST['starttime']) ? $_POST['starttime'] : '';
        $endtime = isset($_POST['endtime']) ? $_POST['endtime'] : '';

        if (!$uid || !$starttime || !$endtime) {
            $this->ajaxReturn(array("type" => "error", "msg" => "缺少必须参数"), 'JSON');
            return;
        }
        $chanxiudata = D('Chanxiudata');

        $endtime=strtotime(date("Y-m-d",strtotime("$endtime  +1   day")));

        $sql = "select count(*) howmany,sum(howlong) as howlong from k_chanxiudata where uid=" . $uid . " and starttime>=" .strtotime($starttime)  . " and endtime<=" . $endtime;

         $r = $chanxiudata->query($sql);

        if ($r) {
            $this->ajaxReturn(array("type" => "success", "msg" => "查询成功", "data" => $r[0]), 'JSON');
            return;
        } else {
            $this->ajaxReturn(array("type" => "error", "msg" => "查询过程出现错误"), 'JSON');
            return;


        }


    }


    public function getPhb()
    {
        $sid1=session("uid");
       // $uid = isset($_POST['uid']) ? $_POST['uid'] : '';
        $starttime = isset($_POST['starttime']) ? $_POST['starttime'] : '';
        $endtime = isset($_POST['endtime']) ? $_POST['endtime'] : '';

//        if (!$uid || !$starttime || !$endtime) {
//            $this->ajaxReturn(array("type" => "error", "msg" => "缺少必须参数"), 'JSON');
//            return;
//        }
        $chanxiudata = D('Chanxiudata');

        $endtime=strtotime(date("Y-m-d",strtotime("$endtime  +1   day")));

        $sql = "SELECT t1.howmany,t1.uid,t1.totaltime as howlong,t2.`nickname`,t2.`companyname` as avatar FROM  (SELECT uid,SUM(howlong) AS totaltime ,count(uid) as howmany FROM `k_chanxiudata` 
WHERE starttime >=".strtotime($starttime)." AND endtime <=".$endtime." GROUP BY uid ) t1,k_member t2 WHERE t1.uid=t2.uid ORDER BY t1.totaltime DESC" ;

        $r = $chanxiudata->query($sql);

        $index=1;
        $list= array();

        foreach ($r as $item){
            $item["order"] =$index;

            if(is_numeric($item["nickname"])&&strlen($item["nickname"])==11){

                $item["nickname"]=substr($item["nickname"],0,4).'****'.substr($item["nickname"],8,11);

            }

            array_push($list,$item);
            $index++;
        }

       // if ($r) {
            $this->ajaxReturn(array("type" => "success", "msg" => "查询成功", "data" => $list), 'JSON');
            return;
      //  } else {
      //      $this->ajaxReturn(array("type" => "error", "msg" => "查询过程出现错误"), 'JSON');
      //      return;


      //  }


    }

}

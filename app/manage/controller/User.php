<?php
namespace app\manage\controller;
use think\Request;
use think\Db;
use think\Session;

class User extends Common
{
	public function _initialize()
	{
	}

    public function index()
    {
        if(Session::get('_supportuser')['id']!=1) {  // 若是二级管理员进入
            $this->error('您无权限访问');
        }
        $pageSize = 10;
        $support_user_list = 
        DB::name('support_user')
        ->field('id,uname,level,addtime')
        ->where('level',1)
        ->whereOr('level',2)
        ->paginate($pageSize);

        // dump($support_user_list);die;
        $this->assign('user_list',$support_user_list);
        $this->assign('page',$support_user_list->render());
        return $this->fetch();
    }
    /**
     * 微信用户
    */
    public function wechatUser()
    {
        $pageSize = 10;
        $wechat_user_list = Db::name('wechat_user')->order('id','desc')->paginate($pageSize);
        $this->assign('user_list',$wechat_user_list);
        $this->assign('page',$wechat_user_list->render());
        return $this->fetch();   
    }
    /**
     * SHA256方式加密密码
     * level 1为超级管理员 2为普通管理员
    */
    public function login(Request $request)
    {
        if($request->method()=='POST') {
            $uname = request()->param('signaccount','','trim,htmlspecialchars,strip_tags');
            $secretinfo = Db::name('support_user')->where('uname',$uname)->find();
            if($secretinfo) {
                $pwd = request()->param('signpwd','','trim,htmlspecialchars,strip_tags');
                if(hash('sha256',$pwd)==$secretinfo['upwd']) {
                    Session::set('_supportuser',$secretinfo);
                    if(Session::has('_supportuser')) {
                        $this->success('登陆成功','/manage/index');
                    }
                } else {
                    $this->error('密码错误');
                }
            } else {
                $this->error('该用户不存在');
            }
        } else {
            if(Session::has('_supportuser')) {
                $this->redirect('/manage/user/login');
            }
            return view();
        }
    }

    /**
     * 退出登录
     * AJAX GET 请求
    */
    public function logout()
    {
        Session::delete('_supportuser');
        if(!Session::has('_supportuser')) {
            $this->success('已登出!','/manage/user/login');
        }
    }
    /**
     * 添加与编辑管理员信息
    */
    public function addSupport(Request $request)
    {
        $method = $request->method();
        if($method=='POST') {
            $params = array(
                'uname'=>$request->post('uname'),
                'level'=>$request->post('support_level/i'),
            );

            if($request->post('id')) {  // 编辑操作对param处理
                if(Session::get('_supportuser')['level']==2) {  // 若是二级管理员修改
                    $params['level']=2;
                }
                if(!$params['level']) {  // 一级非初始管理员修改密码
                    unset($params['level']);
                }
            }

            $i_pwd = $request->post('upwd','','trim,htmlspecialchars,strip_tags');
            if($i_pwd && !$request->post('id')) {  // 操作不是编辑而是添加
                $params['upwd'] = hash('sha256',$i_pwd);
            } else if($i_pwd && $request->post('id')) {  // 是编辑但是也修改密码
                $params['upwd'] = hash('sha256',$i_pwd);
            }
            
            if(!$request->post('id')) {  // 添加
                $params['addtime'] = time();
                // dump($params);die;
                if(Db::name('support_user')->insert($params)){
                    $this->success('添加管理员成功!','/manage/user');
                }
            } else { // 修改
                // dump($params);die;
                if(Db::name('support_user')->where('id',$request->post('id'))->update($params)){
                    if(Session::get('_supportuser')['id']==1) {
                        $this->success('编辑管理员信息成功!','/manage/user');
                    } else {
                       $this->success('密码已修改!','/manage/index'); 
                    }  
                }
            }
            $this->error('操作失败!');
        } else if($method=='GET') {
            return $this->fetch();
        }
    }

    public function Support(Request $request)
    {
        // echo $userid;die;
        $userid = $request->get('token');
        $supportinfo = Db::name('support_user')->where('id',$userid)->find();
        if(!$supportinfo) {
            $this->error('该用户不存在');
        }
        $this->assign('support',$supportinfo);
        return $this->fetch('edit_support');
    }

    public function del($id)
    {
        if($id==1) {
            $this->error('初始用户不允许删除!');
        }
        if(Db::name('support_user')->where('id',$id)->delete()) {
            $this->success('删除管理员成功!','/manage/user');
        } else {
            $this->error('删除管理员失败!');
        }
    }
}

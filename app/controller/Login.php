<?php
declare (strict_types = 1);

namespace app\controller;

use think\Request;
use think\Facade\Db;

class Login
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {


        if ($request->isPost()) {

            //登录操作
            $user_name = $request->param('user_name');
            $password = $request->param('password');
            $captcha = $request->param('captcha');

            //验证码验证
            if(!captcha_check($captcha))
            {
                return json(['code'=>401,'msg'=>'验证码输入错误']);
            }
            //判断用户名
            $admin = Db::table('rbac_admins')->where("user_name",$user_name)->find();
            if(!$admin){
                 return json(['code'=>401,'msg'=>'管理员不存在']);
            }
            if($admin['user_pass'] != admin_pwd($password)){
                return json(['code'=>401,'msg'=>'管理员密码错误']);
            }
            //记录session
            session("admin_id",$admin['id']);
            session("admin_name",$admin['user_name']);

            return json(['code'=>200,'msg'=>'管理员登录成功']);
            
        }


        return view('index');
    }
}

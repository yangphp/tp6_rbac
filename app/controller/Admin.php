<?php
declare (strict_types = 1);

namespace app\controller;

use think\Request;
use think\Facade\Db;

class Admin
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //获取所有管理员
        $admin_list = Db::table("rbac_admins")->alias('a')
                    ->join("rbac_admins_role b","b.admin_id=a.id")
                    ->join("rbac_role c","c.id=b.role_id")
                    ->field(['a.*','b.role_id','c.role_name'])
                    ->select();

        return view('index',['list'=>$admin_list]);
    }

    //添加管理员
    public function add(Request $request)
    {
        if($request->isPost())
        {   
            $data = $request->param();
            
            //添加到数据库
            $admin_info = array(
                'user_name' => $data['user_name'],
                'user_pass' => admin_pwd($data['user_pass']),
                'user_mobile' => $data['user_mobile'],
                'user_email' => $data['user_email'],
                'user_status' => $data['user_status'],
                'is_admin' => 0,
                'add_datetime' => date("Y-m-d H:i:s")
            );

            //判断管理员名称是否存在
            $exist = Db::table('rbac_admins')->where("user_name",$data['user_name'])->count();
            if($exist > 0)
            {
                return json(['code'=>401,'msg'=>'管理员名称已存在,请更换']);
            }

            $admin_id = Db::table('rbac_admins')->insertGetId($admin_info);
            if(!$admin_id){
                return json(['code'=>401,'msg'=>'添加管理员失败']);
            }

            //添加到管理员角色表
            $admin_role_info = array(
                'role_id'   => $data['role_id'],
                'admin_id'  => $admin_id
            );
            $add_res = Db::table('rbac_admins_role')->insert($admin_role_info);
            if (!$add_res) {
               return json(['code'=>401,'msg'=>'添加管理员失败']);
            }

            return json(['code'=>200,'msg'=>'添加管理员成功']);

        }else{

            //获取角色
            $role_list = Db::table("rbac_role")->select();
            return view('add',['roles'=>$role_list]);
        }
        
    }

    //编辑管理员
    public function edit(Request $request,$id)
    {
        if($request->isPost())
        {   
            $data = $request->param();
            
            //修改管理员信息
            $admin_info = array(
                'user_name' => $data['user_name'],
                'user_mobile' => $data['user_mobile'],
                'user_email' => $data['user_email'],
                'user_status' => $data['user_status']
            );
            //密码存在则修改
            if(!empty($data['user_pass'])){
                $admin_info['user_pass'] = admin_pwd($data['user_pass']);
            }

            //判断管理员名称是否存在
            $exist = Db::table('rbac_admins')->where("user_name",$data['user_name'])->where("id",'<>',$id)->count();
            if($exist > 0)
            {
                return json(['code'=>401,'msg'=>'管理员名称已存在,请更换']);
            }
            //修改管理员
            Db::table('rbac_admins')->where('id',$id)->update($admin_info);

            //修改角色
            Db::table('rbac_admins_role')->where("admin_id",$id)->update(['role_id'=>$data['role_id']]);


            return json(['code'=>200,'msg'=>'修改管理员成功']);

        }else{

            //获取角色
            $role_list = Db::table("rbac_role")->select();
            //获取管理员
            $info = Db::table('rbac_admins')->alias('a')
                    ->join("rbac_admins_role b","b.admin_id = a.id")
                    ->field(['a.*','b.role_id'])
                    ->where("a.id",$id)->find();
            return view('edit',['roles'=>$role_list,'info'=>$info]);
        }
        
    }
    //修改状态
    public function status(Request $request,$id)
    {
        $user_status = $request->param('user_status');

        Db::table('rbac_admins')->where("id",$id)->update(['user_status'=>$user_status]);
        return json(['code'=>200,'msg'=>'修改状态成功']);
    }

    //删除管理员
    public function del(Request $request,$id){

        Db::table('rbac_admins')->where("id",$id)->delete();
        Db::table('rbac_admins_role')->where("admin_id",$id)->delete();
        return json(['code'=>200,'msg'=>'删除成功']);
    }
   
}

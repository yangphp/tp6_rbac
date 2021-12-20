<?php
declare (strict_types = 1);

namespace app\controller;

use think\Request;
use think\Facade\Db;

class Role
{
    public function index()
    {

        //获取数据列表
        $role_list = Db::table('rbac_role')->select()->toArray();

        //获取对应角色的用户列表
        foreach($role_list as $key=>$val){
            $admin_list = Db::table('rbac_admins_role')->alias('r')->where("role_id",$val['id'])
                            ->join('rbac_admins a','r.admin_id=a.id')
                            ->field('user_name')
                            ->select()->toArray();
            //将二维数组转换为一维数组
           $admin_arr = array_column($admin_list, 'user_name');
           $role_list[$key]['admin_list'] = implode(",",$admin_arr );
        }

        return view('index',['list'=>$role_list]);
    }
    //添加角色
    public function add(Request $request)
    {
        if($request->isPost()){

            $data = $request->param();
            //添加权限插入数据库
            $role_info = array(
                'role_name' => $data['role_name'],
                'role_info' => $data['role_info']
            );

            $role_id = Db::table('rbac_role')->insertGetId($role_info);
            if(!$role_id){
                return json(['code'=>401,'msg'=>'添加角色失败']);
            }

            //添加到权限表
            $role_auth_info = array(
                'role_id'   => $role_id,
                'auth_ids'  => implode(',',$data['ids'])
            );
            $add_res = Db::table('rbac_role_auth')->insert($role_auth_info);
            if (!$add_res) {
               return json(['code'=>401,'msg'=>'添加角色失败']);
            }

            return json(['code'=>200,'msg'=>'添加角色成功']);


        }else{

            //获取所有权限
            $auths = $this->getAuths();
            return view('add',['auths'=>$auths]);
        }
        
    }

    //添加角色
    public function edit(Request $request,$id)
    {
        if($request->isPost()){

            $data = $request->param();
            //添加权限插入数据库
            $role_info = array(
                'role_name' => $data['role_name'],
                'role_info' => $data['role_info']
            );

            Db::table('rbac_role')->where("id",$id)->update($role_info);
            //添加到权限表
            $role_auth_info = array(
                'auth_ids'  => implode(',',$data['ids'])
            );
            Db::table('rbac_role_auth')->where("role_id",$id)->update($role_auth_info);
  
            return json(['code'=>200,'msg'=>'修改角色成功']);


        }else{

            //获取所有权限
            $auths = $this->getAuths();
            //链表查询 角色和权限
            $role_auth = Db::table('rbac_role')->alias('r')
                        ->join("rbac_role_auth a","r.id=a.role_id")
                        ->where("r.id",$id)
                        ->field(['r.id','r.role_name','r.role_info','a.auth_ids'])
                        ->find();

            $role_auth['auth_ids'] = explode(",",$role_auth['auth_ids']);

            return view('edit',['auths'=>$auths,'info'=>$role_auth]);
        }
        
    }



    //删除角色
    public function del(Request $request,$id){

        //判断是否有管理员账号 
        $count = Db::table('rbac_admins_role')->where("role_id",$id)->count();
        if($count > 0){
            return json(['code'=>401,'msg'=>'删除失败，该角色下面还有管理员未移除']);
        }
        Db::table('rbac_role')->where("id",$id)->delete();
        Db::table('rbac_role_auth')->where("role_id",$id)->delete();
        return json(['code'=>200,'msg'=>'删除权限成功']);
    }

    public function getAuths(){

        //获取所有权限
        $auth = Db::table('rbac_auth')->where("auth_pid",0)->field("id,auth_name")->select()->toArray();

        //获取二级权限
        foreach ($auth as $key => $val) 
        {
            $auth_child = Db::table('rbac_auth')->where("auth_pid",$val['id'])->field("id,auth_name")->select()->toArray();
            $auth[$key]['child'] = $auth_child;

            foreach($auth_child as $k2=>$v2)
            {
                $auth_child2 = Db::table('rbac_auth')->where("auth_pid",$v2['id'])->field("id,auth_name")->select()->toArray();
                $auth[$key]['child'][$k2]['child'] = $auth_child2;
            }
        }
        
        return $auth;
    }
}

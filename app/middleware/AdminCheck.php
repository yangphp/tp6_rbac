<?php
declare (strict_types = 1);

namespace app\middleware;

use think\Facade\View;
use think\Facade\Db;

class AdminCheck
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {

        $admin_id = session('admin_id');

        if (empty($admin_id)) {
            
           return  redirect("/admin/login");
        }
        View::assign("admin_name",session('admin_name'));

        $admin_info = Db::table('rbac_admins')->where("id",$admin_id)->find();

        if($admin_info['is_admin'] == 1)
        {
            //先获取所有的菜单
            $auth_list = Db::table('rbac_auth')->where("auth_pid",0)->select()->toArray();
            foreach ($auth_list as $key => $val) {
                //获取子菜单
                $auth_list[$key]['menus'] = Db::table('rbac_auth')->where("auth_pid",$val['id'])->select()->toArray();
            }
            View::assign("auth_list",$auth_list);
            View::assign("menu_list","all");
        }else{

            $except_url = ['/admin/index'];

            $role_id = Db::table('rbac_admins_role')->where("admin_id",$admin_id)->value('role_id');
            //根据角色获取不同的菜单
            $menu_ids = Db::table('rbac_admins_role')->alias('a')->where("admin_id",$admin_id)
                        ->join("rbac_role_auth b","b.role_id=a.role_id")
                        ->value('auth_ids');

            //获取对应的一级菜单
            $auth_list = Db::table('rbac_auth')->where("auth_pid",0)->where("id","in",$menu_ids)->select()->toArray();
            foreach ($auth_list as $key => $val) {
                //获取子菜单
                $auth_list[$key]['menus'] = Db::table('rbac_auth')->where("auth_pid",$val['id'])->where("id","in",$menu_ids)->select()->toArray();
            }
            View::assign("auth_list",$auth_list);

            //限制客户不能翻墙访问
            $url = $request->url();
            $menu_id = Db::table('rbac_auth')->where("auth_url",$url)->value('id');
            if(!in_array($menu_id,explode(',',$menu_ids)) && !in_array($url,$except_url)){
                exit('无访问权限');
            }

            $url_list = Db::table('rbac_auth')->where("is_menu",0)->where("id",'in',explode(',',$menu_ids))->column('auth_url');
            View::assign("menu_list",$url_list);
        }
        

        //获取当前的url
        $url = $request->url();
        View::assign("auth_url",$url);
        //获取父级id
        $auth_pid = Db::table('rbac_auth')->where("auth_url",$url)->value('auth_pid');
        View::assign("auth_pid",$auth_pid);


        return $next($request);
    }
}

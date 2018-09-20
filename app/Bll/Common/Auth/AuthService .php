<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/19
 * Time: 14:11
 */

namespace App\Bll\Common\Auth;


use App\Model\LoginUser;

class AuthService
{
    
    /**
     * 获取admin信息
     *
     * @param string $login_name 用户名
     * @param string $password 密码
     *
     **/
    public function get_admin_info($login_name, $password)
    {
        try {
            return LoginUser::where([
                'u_name' => $login_name,
                'u_password' => md5($password)
            ])->first();
        } catch (\Exception $e) {
            return response($e->getMessage());
        }
    }
}
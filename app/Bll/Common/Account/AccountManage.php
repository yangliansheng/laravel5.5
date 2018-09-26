<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/20
 * Time: 15:30
 */
namespace App\Bll\Common\Account;

use App\Bll\Enum\AdminerRoleIdEnum;

class AccountManage
{
    /**
     * 登录用户
     * @var \App\Model\LoginUser|null
     */
    protected $User;
    
    /**
     * 用户数据库模型
     * @var \App\Model\LoginUser
     */
    protected $Model;
    
    /**
     * 超管角色标识ID
     * @var int
     */
    private $AdminerId = AdminerRoleIdEnum::超级管理员;
    
    /**
     * AccountManage constructor.
     * 注入登录用户并注入企业管理员数据库模型
     * @param \App\Model\LoginUser|null $LoginUser
     */
    public function __construct(\App\Model\LoginUser $LoginUser)
    {
        $this->User = $LoginUser;
        $this->Model = new \App\Model\LoginUser();
    }
    
    /**
     * @param $params
     * 更新用户密码
     * @return bool
     */
    public function updatePassword($params) {
        if($params['u_name'] != $this->User->u_name) {
            return ['res' => false,'msg' => '非法的修改'];
        }
        $oldpassword = $this->Model->getAuthIdentifierName(['u_name'=>$this->User->u_name]);
        if($params['newpassword'] == $oldpassword) {
            return ['res' => true,'msg' => ''];
        }
        if($params['password'] != $oldpassword) {
            return ['res' => false,'msg' => '密码输入错误'];
        }
        $res = $this->Model->where('u_name',$this->User->u_name)->update(['u_password'=>$params['newpassword']]);
        return ['res' => $res?$res:false,'msg' => ''];
    }
    
    /**
     * 是否超级管理员身份
     * @return bool
     */
    public function isAdminer() {
        if($this->User->role_id == $this->AdminerId) {
            return true;
        }
        return false;
    }
    
    
}
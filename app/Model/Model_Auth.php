<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/17
 * Time: 16:58
 */

namespace App\Model;

/**
 * 用户数据访问控制器--中间类库业务依赖注入
 * Class Model_Auth
 * @package App\Model
 */
class Model_Auth
{
    /**
     * 注入后台管理用户实例
     * @var AdminUser
     */
    protected $Adminer;
    
    /**
     * 注入第三方登录用户实例
     * @var LoginUser
     */
    protected $LoginUser;
    
    /**
     * Model_Auth constructor.
     * 依赖处理
     * @param AdminUser $adminUser
     * @param LoginUser $loginUser
     */
    public function __construct(AdminUser $adminUser,LoginUser $loginUser) {
        if(empty($adminUser) || empty($loginUser)) {
            throw_unless(false, ModelException::class);
        }
        $this->Adminer = $adminUser;
        $this->LoginUser = $loginUser;
    }
}
<?php

namespace App\Http\Controllers;

use App\Model\AdminUser;
use App\Model\LoginUser;
use App\Model\Model_Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function __construct()
    {
//        app()->bind();//绑定每次创建新实例
        app()->singleton('ModelAuth', function(){
            return new Model_Auth(new AdminUser(),new LoginUser());
        });//注入绑定的数据中间验证层实例
        config(['database.module_connection'=>'mysql']);
    }
    
    /**
     * @param string $data
     * @param int $code
     * @param string $msg
     * 响应的二次封装增加响应内容和状态码和错误信息
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function response($data = '',$code = 1,$msg = '') {
        return response(['data'=>$data,'code'=>$code,'msg'=>$msg]);
    }
}

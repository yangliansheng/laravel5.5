<?php

namespace App\Http\Middleware\Common;

use App\Model\LoginUser;
use Tymon\JWTAuth\Exceptions\JWTException;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class APIToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // # 过滤内网
        // $ip = $request->getClientIp();
        // # 获取IP白名单
        // $white_list = explode(',', env('WHITE_HOST'));
        // if (!in_array($ip, $white_list)) {
        //     return error(403);
        // }
        try {
            $token = JWTAuth::parseToken()->authenticate();
//            $token = JWTAuth::parseToken()->getToken();
            $AdminUser = JWTAuth::toUser($token);
            $DataBase = 'mysql_'.$AdminUser->c_prefix;
            config(['database.module_connection'=>$DataBase]);
            $LoginUser = \Auth::guard('api')->user();
            $this->check_loginuser($LoginUser);
            config(['user.adminer'=>$AdminUser]);
            config(['user.loginer'=>$LoginUser]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response([
                'status_code'=>402,
                'message'=>$e->getMessage()
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            try {
                JWTAuth::getToken()->get();//验证是否能获取到token
                $newToken = JWTAuth::refresh();
            } catch (\Exception $e) {
                return response([
                    'status_code'=>402,
                    'message'=>$e->getMessage()
                ]);
            }
            #刷新token并且返回新token
            return response([
                'result'=>[
                    'newToken' => $newToken
                ],
                'status_code'=>-406,
                'message'=>config('exception_code.-406')
            ]);
        } catch (JWTException $e) {
            return response([
                'status_code'=>402,
                'message'=>$e->getMessage()
            ]);
        }
       
        return $next($request);
    }
    
    /**
     * 检查登录用户的密码是否修改，如果修改登出系统
     * @param LoginUser $loginUser
     */
    protected function check_loginuser(LoginUser $loginUser) {
//        if($loginUser->u_password != LoginUser::getTheAuthPassword(['u_name'=>$loginUser->u_name])){
//            \Auth::guard('api')->logout();
//            $newToken = JWTAuth::refresh();
//
//            return redirect('/');
//        }
        return;
    }
}

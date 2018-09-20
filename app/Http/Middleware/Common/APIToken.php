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
            $this->check_token($request->all());
            $token = JWTAuth::parseToken()->authenticate();
//            $token = JWTAuth::parseToken()->getToken();
            $AdminUser = JWTAuth::toUser($token);
            $DataBase = 'mysql_'.$AdminUser->c_prefix;
            config(['database.module_connection'=>$DataBase]);
            $LoginUser = \Auth::guard('api')->user();
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
    
    protected function check_token($arr) {
        /*********** api传过来的token  ***********/
        if (!isset($arr['token']) || empty($arr['token'])) {
            return response([
                'status_code'=>-101,
                'message'=>config('exception_code.-101')
            ]);
        }
        $app_token = $arr['token']; // api传过来的token
        /*********** 服务器端生成token  ***********/
        unset($arr['token']);
        $service_token = '';
        foreach ($arr as $key => $value) {
            $service_token .= md5($value);
        }
        $service_token = md5(config('app.login_begin'). $service_token .config('app.login_end')); // 服务器端即时生成的token
        /*********** 对比token,返回结果  ***********/
        if ($app_token !== $service_token) {
            $this->return_msg(1,'Token is not correct');
        }
    }
}

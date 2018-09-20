<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/18
 * Time: 10:32
 */

namespace App\Http\Controllers\Common;

use App\Bll\Common\Auth\AuthenticatesUsers;
use App\Bll\Common\Auth\AuthService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends BaseController
{
    use ValidatesRequests,AuthenticatesUsers;
    protected $auth, $authService;
    protected $admin;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
        $this->authService = AuthService::class;
    }
    
    /**
     * @return string
     */
    public function username()
    {
        return 'c_code';
    }
    
}
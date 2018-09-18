<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/18
 * Time: 11:20
 */
namespace App\Bll\Common\Auth;

use App\Model\AdminUser;
use App\Model\LoginUser;
use App\Model\Model_Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\ModelAuth;
use Illuminate\Validation\ValidationException;

trait AuthenticatesUsers
{use ThrottlesLogins;
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            
            return $this->sendLockoutResponse($request);
        }
        //验证通过
        if ($this->attemptLogin($request)) {
            $AdminUser = $this->sendLoginResponse($request);
            if($AdminUser) {
                $DataBase = 'mysql_'.$AdminUser->c_prefix;
                config(['database.module_connection'=>$DataBase]);
                $LoginUser = LoginUser::where('u_name',$request->u_name)
                    ->where('u_password',$request->u_password)
                    ->first();
                if($LoginUser) {
                    config(['user.adminer'=>$AdminUser]);
                    config(['user.loginer'=>$LoginUser]);
                    $ModelAuth = new Model_Auth($AdminUser,$LoginUser);
                    return response(['data'=>object_to_array($ModelAuth),'code'=>0,'msg'=>'']);
                }else{
                    $this->incrementLoginAttempts($request);
                    return $this->sendFailedLoginResponse($request,-2);
                }
            }
        }else{
            // If the login attempt was unsuccessful we will increment the number of attempts
            // to login and redirect the user back to the login form. Of course, when this
            // user surpasses their maximum number of attempts they will get locked out.
            $this->incrementLoginAttempts($request);
            return $this->sendFailedLoginResponse($request,-1);
        }
    }
    
    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
        ]);
    }
    
    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }
    
    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username());
    }
    
    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        
        $this->clearLoginAttempts($request);
        
        $adminer = AdminUser::where('c_code',$request->c_code)->first();
        return $adminer;
    }
    
    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //
    }
    
    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws ValidationException
     */
    protected function sendFailedLoginResponse(Request $request,$code)
    {
        return response(['data'=>[],'code'=>$code,'msg'=>config('exception_code.'.$code)]);
    }
    
    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        
        $request->session()->invalidate();
        
        return redirect('/');
    }
    
    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
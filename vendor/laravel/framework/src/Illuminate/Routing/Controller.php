<?php

namespace Illuminate\Routing;

use App\Http\Controllers\ResponseHandler;
use BadMethodCallException;

abstract class Controller
{
    /**
     * The middleware registered on the controller.
     *
     * @var array
     */
    protected $middleware = [];
    
    /**
     * 响应实例
     * @var ResponseHandler
     */
    private $resp;
    
    /**
     * 登录用户实例
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $LoginUser;
    
    /**
     * 绑定Http响应对象
     * Controller constructor.
     */
    public function __construct()
    {
        $this->resp = new ResponseHandler();
    }
    
    /**
     * Register middleware on the controller.
     *
     * @param  array|string|\Closure  $middleware
     * @param  array   $options
     * @return \Illuminate\Routing\ControllerMiddlewareOptions
     */
    public function middleware($middleware, array $options = [])
    {
        foreach ((array) $middleware as $m) {
            $this->middleware[] = [
                'middleware' => $m,
                'options' => &$options,
            ];
        }
        return new ControllerMiddlewareOptions($options);
    }

    /**
     * Get the middleware assigned to the controller.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Execute an action on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        return call_user_func_array([$this, $method], $parameters);
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException("Method [{$method}] does not exist on [".get_class($this).'].');
    }
    
    /**
     * 获取绑定的Http响应对象
     * @return ResponseHandler
     */
    protected function response()
    {
        return $this->resp;
    }
    
    /**
     * 绑定登录用户实例--中间件执行在构造之后
     */
    protected function bindingUser() {
        $this->LoginUser =  config('user.loginer');
    }
}

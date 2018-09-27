<?php
/**
 * 作者：yls
 * 功能：扩展框架Response的功能
 * 最后修改时间： 2018-09-20
 */
namespace App\Http\Controllers;

use Exception;

/**
 * Class ResponseHandler 包装Response响应对像
 * @package App\Http\Controllers
 */
class ResponseHandler {

    protected $response;

    public  function __construct () {
        $this->response = app('\Illuminate\Routing\ResponseFactory');
    }

    /**
     * 响应一个http的成功请求
     * @param object $data  要返回的数据
     * @param int $code   成功代码，一般为0 如果传其它代码，客户端将以错误处理
     * @return 返回一个response的对像
     */
    public function success ($data, $code = 0) {
        $result = array();
        if (is_string($data))
            $result['message'] = $data;
        else
            $result['result'] = $data;
        $result['status_code'] = $code;
        return $this->response->json($result);
    }

    /**
     * 响应一个http的失败请求
     * @param string $msg 要返回给客户端的消息
     * @param int $code 要返回客户端的错误代码
     * @return 引发一个http请求的错误异常
     */
    public function error ($msg, $code = -200) {
        $result = array();
        $result['message'] = (string)$msg;
        $result['status_code'] = $code;
        return $this->response->json($result);
    }

    /**
     * 将一个程序错误转换成http错误响应
     * @param Exception $ex php的错误
     * @return 返回错误异常
     */
    public function responseException (Exception $ex) {
        $code = $ex->getCode();
        if ($code == 0)
            $code = -500;
        return $this->success($ex->getMessage(), $code);
    }

    public function __call ($name, $arguments) {
        return call_user_func_array([$this->response, $name], $arguments);
    }

    public function  __set ($name, $value) {
        $this->response->$name = $value;
    }

    public function  __get ($name) {
        return $this->response->$name;
    }

    public function __isset ($name) {
        return isset($this->response->$name);
    }

    public function __unset ($name) {
        unset($this->response->$name);
    }
}
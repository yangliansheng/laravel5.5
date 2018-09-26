<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/17
 * Time: 16:32
 */

return [
    -500=>'系统错误',
    -406=>'无效的Token',
    -100=>'错误的数据库访问',
    -101=>'未登记的访问用户',
    -1  =>'非法的公司编号',
    -2  =>'登录账号或密码错误',
    -200=>'入参校验错误',
    -102=>'权限不足'
]+\Symfony\Component\HttpFoundation\Response::$statusTexts;
<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/25
 * Time: 15:27
 */
namespace App\Bll\Enum;

class LogTypeEnum extends Enum
{
    const 新增 = 1;
    const 修改 = 2;
    const 删除 = 3;
    const 营业 = 4;
    const 关停 = 5;
}
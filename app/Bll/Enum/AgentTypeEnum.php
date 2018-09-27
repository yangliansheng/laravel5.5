<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/25
 * Time: 15:19
 */
namespace App\Bll\Enum;

class AgentTypeEnum extends Enum
{
    const 身份证 = 0;
    const 驾驶证 = 1;
    const 护照 = 2;
    const 居留证 = 3;
}
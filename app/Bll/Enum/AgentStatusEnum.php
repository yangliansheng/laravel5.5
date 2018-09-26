<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/25
 * Time: 15:22
 */

namespace App\Bll\Enum;

class AgentStatusEnum extends Enum
{
    const 待入司 = 0;
    const 在职 = 1;
    const 考核保护 = 2;
    const 冻结 = 3;
    const 预离司 = 4;
    const 离司 = 5;
}
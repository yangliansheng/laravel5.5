<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/25
 * Time: 15:29
 */
namespace App\Bll\Enum;

class TeamStatusEnum extends Enum
{
    const 组织状态_无效 = 0;
    const 组织状态_有效 = 1;
    const 组织状态_迁移中 = 2;
}
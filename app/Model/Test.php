<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Test extends BaseModel
{
    protected $table = 'bao_company'; // 默认 flights
    protected $primaryKey = 'u_id'; // 默认 id
//    public $incrementing = false; // 当你的主键不是自增或不是int类型
//    public $keyType = 'string'; // 当你的主键不是整型
//    public $timestamps = false; // 不自动维护created_at 和 updated_at 字段
# protected $dateFormat = 'U'; // 自定义自己的时间戳格式
    
}

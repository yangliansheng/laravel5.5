<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LoginUser extends Model
{
    protected $table = 'c_account'; // 默认 flights
    protected $primaryKey = 'u_id'; // 默认 id
    
    protected $hidden = [
        'u_password','remember_token'
    ];
    /**
     * @param $params
     * 根据登录用户名获取登录密码
     * @return Model|null|object|string|static
     */
    protected function getAuthPassword($params) {
        if(isset($params['u_name'])) {
           return self::where('u_name',$params['u_name'])->first();
        }
        return '';
    }
}

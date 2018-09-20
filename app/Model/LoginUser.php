<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class LoginUser extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $table = 'c_account'; // 默认 flights
    protected $primaryKey = 'u_id'; // 默认 id
    protected $guard = 'api';
    protected $hidden = [
        'u_password','remember_token'
    ];
    
    /**
     * @param $params
     * 根据登录用户名获取登录密码
     * @return Model|null|object|string|static
     */
    public static function getTheAuthPassword($params) {
        if(isset($params['u_name'])) {
           return self::where('u_name',$params['u_name'])->first()->u_password;
        }
        return '';
    }
    
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function getAuthIdentifierName()
    {
        return $this->primaryKey;
    }
}

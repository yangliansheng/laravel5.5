<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable implements JWTSubject
{
    protected $table = 'bao_company'; // 默认 flights
    protected $primaryKey = 'c_id'; // 默认 id
    protected $guard = 'admin';
    
    use Notifiable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'c_id', 'c_code', 'o_code', 'created_at', 'updated_at', 'c_prefix'
    ];
    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'c_id'
    ];
    
    /**
     * 获取验证类的验证密码
     * @return string
     */
    public function getAuthPassword() {
        return 'adminer';
    }
    
    public function getAuthIdentifierName() {
        return 'c_id';
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
}

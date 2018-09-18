<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $table = 'bao_company'; // 默认 flights
    protected $primaryKey = 'u_id'; // 默认 id
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
}

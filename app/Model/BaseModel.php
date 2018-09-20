<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/17
 * Time: 16:10
 */

namespace App\Model;

use App\Exceptions\ModelAuthException;
use App\Exceptions\ModelException;
use Illuminate\Database\Eloquent\Model;

/**
 * 封装数据模型中间类处理自动切换数据库检测
 * Class BaseModel
 * @package App\Model
 */
class BaseModel extends Model
{
    /**
     * 数据库链接
     * @var \Illuminate\Config\Repository|mixed|string
     */
    protected $connection = '';
    
    /**
     * BaseModel constructor.
     * 取全局数据库连接对象
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $connection = config('database.module_connection');
        if($connection) {
            $this->connection = $connection; // 为模型指定不同的连接
        }else{
            throw_unless(false, ModelException::class);
        }
//        $this->handle(app('ModelAuth'));
    }
    
    /**
     * 数据库隔离对象注入验证
     * @param $auther
     */
    public function handle($auther) {
        if(!$auther instanceof Model_Auth) {
            throw_unless(false, ModelAuthException::class);
        }
    }
}
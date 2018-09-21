<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/20
 * Time: 11:41
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProductRate extends Model{
    
    protected $table = 'c_p_rate'; // 默认 flights
    protected $primaryKey = 'r_id';
    public $timestamps = false; // 不自动维护created_at 和 updated_at 字段
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public function findOne($id){
        $data = \DB::select("select * from " . $this->getTable() . " where " . $this->primaryKey . "=?", [$id]);
        return $data;
    }
    
    public function findOneByPid($p_id){
        $data = \DB::select("select * from " . $this->getTable() . " where p_id =? ", [$p_id]);
        return $data;
    }
}
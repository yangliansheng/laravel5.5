<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/21
 * Time: 10:41
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Policy extends Model{
    
    protected $table = 'c_insurance_slip'; // 默认 flights
    protected $primaryKey = 's_id';
    public $timestamps = false; // 不自动维护created_at 和 updated_at 字段
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public function findOne($s_id){
        $data = \DB::select("select * from ".$this->getTable()." where " . $this->primaryKey . "=?", [$s_id]);
        return $data;
    }
}

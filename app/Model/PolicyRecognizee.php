<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/26
 * Time: 10:37
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PolicyRecognizee extends Model{
    
    protected $table = 'c_insurance_recognizee'; // 默认 flights
    protected $primaryKey = 'ir_id';
    public $timestamps = false; // 不自动维护created_at 和 updated_at 字段
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public function findOne($s_id){
        $data = \DB::select("select * from ".$this->getTable()." where s_id = ?", [$s_id]);
        return empty($data) ? [] : $data[0];
    }
}

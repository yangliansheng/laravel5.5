<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/18
 * Time: 9:49
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Company extends Model{

    protected $table = 'c_insurance_company'; // 默认 flights
    protected $primaryKey = 'c_id';
    public $timestamps = false; // 不自动维护created_at 和 updated_at 字段

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function findOne($id){
        $data = \DB::select("select * from ".$this->getTable()." where c_id=:c_id limit 1", ['c_id' => $id]);
        return empty($data) ? [] : $data[0];
    }
}
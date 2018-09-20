<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/19
 * Time: 14:19
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Product extends Model{

    protected $table = 'c_product'; // 默认 flights
    protected $primaryKey = 'p_id';
    public $timestamps = false; // 不自动维护created_at 和 updated_at 字段

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function findOne($id){
        $data = \DB::select("select * from " . $this->getTable() . " where " . $this->primaryKey . "=?", [$id]);
        return $data;
    }
}
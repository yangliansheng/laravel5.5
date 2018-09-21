<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'c_organization'; // 默认 flights
    protected $primaryKey = 'o_id'; // 默认 id
    
    /**
     * 获取表信息
     * @return string
     */
    public static function getTableName() {
        $Model = new self();
        return $Model->getTable();
    }
    
    /**
     * 获取主键信息
     * @return string
     */
    public static function getPrimaryKey() {
        $Model = new self();
        return $Model->getKeyName();
    }
    
    /**
     * @param $name
     * 根据等级名称获取该等级的机构信息
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getOrganizationsByGradeId($o_g_id) {
        return self::where('o_g_id',$o_g_id)->get();
    }
    
}

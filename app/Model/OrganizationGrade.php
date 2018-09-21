<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrganizationGrade extends Model
{
    protected $table = 'c_organization_grade'; // 默认 flights
    protected $primaryKey = 'o_g_id'; // 默认 id
    
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
     * 根据等级名称获取等级信息
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getOrganizationGradesByName($name) {
        return self::where('o_g_name',$name)->get();
    }
     /**
     * @param $name
     * 根据等级排序获取等级信息
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getOrganizationGradesBySort($sort) {
        return self::where('o_g_sort',$sort)->get();
    }
    
}

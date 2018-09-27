<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'c_organization'; // 默认 flights
    protected $primaryKey = 'o_id'; // 默认 id
    protected $hidden = ['o_path','o_depth','updated_at','created_at'];
    
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
    
    /**
     * @param $name
     * 根据机构代码获取机构信息
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getOrganizationsByCode($o_code) {
        return self::where('o_code',$o_code)->first();
    }
    
}

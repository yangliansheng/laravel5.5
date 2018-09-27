<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrganizationGradeLog extends Model
{
    protected $table = 'c_organization_grade_log'; // 默认 flights
    protected $primaryKey = 'l_id'; // 默认 id
    
    /**
     * @param $data
     * 记录机构类型操作日志
     * @return bool
     */
    public static function addnew($data) {
        $log = new self();
        $log->l_type = $data['type'];
        $log->l_describe = $data['describe'];
        $log->l_adminer = $data['adminer'];
        return $log->save();
    }
}

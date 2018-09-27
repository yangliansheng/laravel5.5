<?php

namespace App\Model;

use App\Bll\Enum\AgentStatusEnum;
use App\Bll\Enum\TeamStatusEnum;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $table = 'c_agent'; // 默认 flights
    protected $primaryKey = 'ag_id'; // 默认 id
    protected $hidden = [];
    
    /**
     * @param $ids
     * 根据组织id或ID组获取组织下未离职的人员集合
     * @return $this|array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getAgentsFromTeamIdsWhereNotDimission($ids) {
        $data = [];
        if($ids) {
            if(is_string($ids)) {
                $data = static::where('t_id',$ids);
            }
            if(is_array($ids)) {
                $data = static::whereIn('t_id',$ids);
            }
            $data = $data->where('ag_status','!=',AgentStatusEnum::离司)->get();
        }
        return $data;
    }
}

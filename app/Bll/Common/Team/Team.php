<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/26
 * Time: 11:39
 */

namespace App\Bll\Common\Team;

use App\Bll\Common\Organization\Organizations;
use App\Bll\Enum\TeamDirectlyEnum;
use App\Bll\Enum\TeamExistEnum;
use App\Bll\Enum\TeamGradeEnum;
use App\Model\Agent;
use App\Model\JobGrade;
use App\Model\Organization;

class Team
{
    /**
     * 登录用户
     * @var \App\Model\LoginUser|null
     */
    protected $User;
    
    /**
     * 企业实例
     * @var null
     */
    protected $SystemUser;
    
    /**
     * 组织数据库模型实例
     * @var Organization
     */
    protected $TeamModel;
    
    /**
     * 正常组织集合
     * @var
     */
    protected $Teams;
    
    
    /**
     * 注入登录用户并注入企业管理员数据库模型
     * 注入一个组织数据库实例
     * Organizations constructor.
     */
    public function __construct(\App\Model\LoginUser $LoginUser,$adminUser = null) {
        $this->User = $LoginUser;
        $this->SystemUser = $adminUser;
        $this->TeamModel = new \App\Model\Team();
        $this->Teams = $this->TeamModel->where('t_is_exist',TeamExistEnum::组织_正常);
    }
    
    /**
     * @param $property
     * 实例操作
     * @return mixed
     */
    public function __get($property) {
        return $this->TeamModel->$property;
    }
    
    /**
     * 实例操作
     * @param $property
     * @param $value
     */
    public function __set($property, $value) {
        $this->TeamModel->$property = $value;
    }
    
    /**
     * @param $params
            'o_id' => 'present|integer|nullable',
            't_name' => 'present|string|nullable',
            't_code' => 'present|string|nullable',
            't_status' => 'present|integer|nullable|in:'.TeamStatusEnum::组织状态_无效.','.TeamStatusEnum::组织状态_有效.','.TeamStatusEnum::组织状态_迁移中.'',
            't_grade' => 'present|integer|nullable|in:'.TeamGradeEnum::区.','.TeamGradeEnum::部.'',
            'ag_name' => 'present|string|nullable',
            't_pname' => 'present|string|nullable',
     * @param $isAdminer
     * @return array
     */
    public function index($params,$isAdminer) {
        $Teams = $this->Teams;
        $Organizations = new Organizations($this->User);
        $allow_organization_ids = $Organizations->getAllowShowOrganizations($isAdminer);
        if($params['o_id']) {
            //是否是关停的机构
            if(!Organization::find($params['o_id'])->o_status) {
                return ['res'=>false,'msg'=>'查询机构已被关停'];
            }
            //如果传入机构id,判断机构是否有权限查询
            if($isAdminer || in_array($params['o_id'],$allow_organization_ids)) {
                $Teams = $Teams->where('o_id',$params['o_id']);
            }else{
                return ['res'=>false,'msg'=>config('exception_code.-102')];
            }
        }else{
            if(!$isAdminer) {
                $Teams = $Teams->whereIn('o_id',$allow_organization_ids);
            }
        }
        //组织名称有下拉传入id参数否则传入名称字符串
//        if($params['t_id']) {
//            $Teams = $Teams->where('t_id',$params['t_id']);
//        }
        if($params['t_name']) {
            $Teams = $Teams->where('t_name','like','%'.$params['t_name'].'%');
        }
        if($params['t_code']) {
            $Teams = $Teams->where('t_code',$params['t_code']);
        }
        if($params['t_status']) {
            $Teams = $Teams->where('t_status',$params['t_status']);
        }
        if($params['t_grade']) {
            $Teams = $Teams->where('t_grade',$params['t_grade']);
        }
        if($params['ag_name']) {
            $Teams = $Teams->where('t_ag_name','like','%'.$params['ag_name'].'%');
        }
        //上级组织名称有下拉传入id参数否则传入名称字符串
//        if($params['t_pid']) {
//            $Teams = $Teams->where('t_pid',$params['t_pid']);
//        }
        if($params['t_pname']) {
            $t_ids = array_column($this->Teams->where('t_name','like','%'.$params['t_pname'].'%')->get()->toArray(),'t_id');
            $Teams = $Teams->whereIn('t_id',$t_ids);
        }
        if(isset($params['page']) && $params['page']) {
            $list = $Teams->paginate($this->TeamModel->getPerPage());
        }else{
            $list = $Teams->get();
        }
        return ['res'=>true,'data'=>$list];
    }
    
    
    /**
     * @param $isAdminer
     * 根据登录用户获取可管理的组织集合
     * @return $this|\Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function getAllAllowTeamsByLoginUser($isAdminer) {
        $Teams = $this->Teams;
        if($isAdminer) {
            $Teams = $Teams->get();
        }else{
            $Organizations = new Organizations($this->User);
            $allow_organization_ids = $Organizations->getAllowShowOrganizations($isAdminer);
            $Teams = $Teams->whereIn('o_id',$allow_organization_ids)->get();
            
        }
        return $Teams;
    }
    
    /**
     * @param $id
     * @param $isAdminer
     * 获取组织详情
     * @return array
     */
    public function show($id, $isAdminer) {
        if(in_array($id,array_column($this->getAllAllowTeamsByLoginUser($isAdminer)->toArray(),'t_id'))) {
           $data = $this->TeamModel->find($id);
           if($data->count()) {
                $data->o_name = Organization::find($data->o_id)->o_name;//所属机构名
                $t_p_team = $this->TeamModel->find($data->t_pid);
                $data->t_pname = $t_p_team?$t_p_team->t_name:'';//上级机构名
                $data->t_pgrade = $t_p_team?TeamGradeEnum::search($t_p_team->t_grade):'';//上级机构级别
                $data->t_grade = TeamGradeEnum::search($data->t_grade);//机构级别
                $data->t_directly = TeamDirectlyEnum::search($data->t_directly);//机构级别
                $agent = Agent::find($data->t_ag_id);
                $data->t_ag_phone = $agent?$agent->ag_phone:'';
                if($agent && $agent->jg_id) {
                    $job = JobGrade::find($agent->jg_id);
                    $data->t_ag_job_grade = $job->count()?$job->j_g_name:'';
                }else{
                    $data->t_ag_job_grade = '';
                }
               $data->t_ag_phone = $agent?$agent->ag_phone:'';
           }
           return ['res'=>true,'data'=>$data];
        }
        return ['res'=>false,'msg'=>'无权限查看组织详情'];
    }
    
}
<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/21
 * Time: 15:22
 */

namespace App\Bll\Common\Organization;

use App\Bll\Enum\AgentStatusEnum;
use App\Bll\Enum\OrganizationStatus;
use App\Bll\Enum\TeamStatusEnum;
use App\Model\Agent;
use App\Model\Organization;
use App\Model\Team;

class Organizations
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
     * 机构数据库模型实例
     * @var Organization
     */
    private $OrganizationModel;
    
    /**
     * 注入登录用户并注入企业管理员数据库模型
     * 注入一个机构数据库实例
     * Organizations constructor.
     */
    public function __construct(\App\Model\LoginUser $LoginUser,$adminUser = null) {
        $this->User = $LoginUser;
        $this->SystemUser = $adminUser;
        $this->OrganizationModel = new \App\Model\Organization();
    }
    
    /**
     * @param $property
     * 实例操作
     * @return mixed
     */
    public function __get($property) {
        return $this->OrganizationModel->$property;
    }
    
    /**
     * 实例操作
     * @param $property
     * @param $value
     */
    public function __set($property, $value) {
        $this->OrganizationModel->$property = $value;
    }
    
    /**
     * @param $params
     * @param $isAdminer
     * 获取机构列表
     * @return array
     */
    public function getList($params,$isAdminer) {
        $OrganizationModel = $this->OrganizationModel;
        //根据登录用户获取本机构与有关系的下级机构
        if(!$isAdminer) {
            $thisOrganization = Organization::getOrganizationsByCode($this->User->o_code);
            $path = $thisOrganization->o_path.','.$thisOrganization->o_id;
            $OrganizationModel = $OrganizationModel->where('o_path','like', $path.'%');
            $Grades = new OrganizationGrade();
            $allow_o_g_ids = array_column($Grades->getLowLevelAndEqualListByLoginUser($this->User)->toArray(),'o_g_id');
            if($params['o_g_id']) {
                //判断当前传入的等级是否可以查询
                if(!in_array($params['o_g_id'],$allow_o_g_ids)) {
                    return ['res'=>false,'msg'=>'查询的机构等级超过可以查询的权限'];
                }
                $OrganizationModel = $OrganizationModel->where('o_g_id', $params['o_g_id']);
            }else{
                $OrganizationModel = $OrganizationModel->whereIn('o_g_id', $allow_o_g_ids);
            }
        }else{
            if($params['o_g_id']) {
                $OrganizationModel = $OrganizationModel->where('o_g_id', $params['o_g_id']);
            }
        }
        if($params['o_status'] !== null) {
            $OrganizationModel = $OrganizationModel->where('o_status', $params['o_status']);
        }
        if($params['o_name'] !== '') {
            $OrganizationModel = $OrganizationModel->where('o_name', 'like', '%'.$params['o_name'].'%');
        }
        $list = $OrganizationModel->orderBy('o_depth')->get();
        return ['res'=>true,'data'=>$list];
    }
    
    /**
     * 获取所有未关停的机构的集合
     * @return array
     */
    public function getAllNotClose() {
        $list = $this->OrganizationModel->where('o_status',OrganizationStatus::营业)->get();
        return ['res'=>true,'data'=>$list];
    }
    
    /**
     * 获取当前登录用户可访问的机构的机构id集合
     * @return array
     */
    public function getAllowShowOrganizations($isAdminer) {
        if($isAdminer) {
            $ids = array_column(Organization::all()->toArray(),'o_id');
        }else{
            $OrganizationModel = $this->OrganizationModel;
            $thisOrganization = Organization::getOrganizationsByCode($this->User->o_code);
            $path = $thisOrganization->o_path.','.$thisOrganization->o_id;
            $OrganizationModel = $OrganizationModel->where('o_path','like', $path.'%');
            $ids = array_column($OrganizationModel->get()->toArray(),'o_id')+[$thisOrganization->o_id];
        }
        return $ids;
    }
    
    /**
     * @param $arr
     * 更新机构信息
     * @return array
     */
    public function update($arr,$id,$isAdminer) {
        $thisOrganization = Organization::getOrganizationsByCode($this->User->o_code);
        if($arr['o_status'] == 0) {
            //处理关停状态
            //无下辖机构(或下辖机构都为关停)
            if($this->getNoCloseUnderOrganizations()->count()) {
                return ['res'=>false,'msg'=>'存在营业的下辖机构无法关停当前机构'];
            }
            //无下辖机组织(或下辖组织都为无效)
            if($this->getNoCloseUnderTeams()->count()) {
                return ['res'=>false,'msg'=>'存在有效的组织无法关停当前机构'];
            }
            //无代理人成员(或人员都是离职)
            if($this->getNoLeaveAgents()->count()) {
                return ['res'=>false,'msg'=>'机构下有正常在职的人员无法关停当前机构'];
            }
            
        }
        $Organization = $this->OrganizationModel->where('o_id',$id);
        if(!$Organization->first()) {
            return ['res'=>false,'msg'=>'未找到机构'];
        }
        if($isAdminer) {
            $res = $Organization->update([
                'o_user'=>$arr['o_user'],
                'o_phone'=>$arr['o_phone'],
                'o_status'=>$arr['o_status'],
            ]);
        }else if($id != $thisOrganization->o_id) {
            return ['res'=>false,'msg'=>'无权更新其他机构信息'];
        }else{
            $res = $Organization->where('o_id',$id)->update([
                'o_user'=>$arr['o_user'],
                'o_phone'=>$arr['o_phone'],
                'o_status'=>$arr['o_status'],
            ]);
        }
        return ['res' => $res?$res:false,'msg' => ''];
    }
    
    /**
     * 获取当前登录用户管理机构下辖的未关停机构
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function getNoCloseUnderOrganizations() {
        $OrganizationModel = $this->OrganizationModel;
        $thisOrganization = Organization::getOrganizationsByCode($this->User->o_code);
        $path = $thisOrganization->o_path.','.$thisOrganization->o_id;
        $OrganizationModel = $OrganizationModel->where('o_path','like', $path.'%')->where('o_status',OrganizationStatus::营业);
        $lists = $OrganizationModel->get();
        return $lists;
    }
    
    /**
     * 获取当前登录用户管理机构下辖的非无效状态的组织
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function getNoCloseUnderTeams() {
        $thisOrganization = Organization::getOrganizationsByCode($this->User->o_code);
        $teams = Team::where('o_id',$thisOrganization->o_id)->where('t_status','!=',TeamStatusEnum::组织状态_无效)->get();
        return $teams;
    }
    
    /**
     * 获取当前登录用户管理机构下辖的未离职的代理人
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function getNoLeaveAgents() {
        $thisOrganization = Organization::getOrganizationsByCode($this->User->o_code);
        $agents = Agent::where('o_id',$thisOrganization->o_id)->where('ag_status','!=',AgentStatusEnum::离司)->get();
        return $agents;
    }
    
    /**
     * @param $arr
     * 新增机构信息
     * @return array
     */
    public function store($arr) {
        //机构名不能重名
        if(Organization::where('o_name',$arr['o_name'])->get()->count()) {
            return ['res'=>false,'msg'=>'机构名称已存在'];
        }
        //如果第一次录入机构，机构类型等级必须为总公司级别
        $count = Organization::all()->count();
        $first_o_g_id = \App\Model\OrganizationGrade::orderBy('o_g_sort')->first()->o_g_id;
        if(!$count && $arr['o_g_id'] != $first_o_g_id) {
            return ['res'=>false,'msg'=>'没有任何机构请先录入最高层级的机构类型'];
        }
        if($count && $arr['o_g_id'] == $first_o_g_id) {
            return ['res'=>false,'msg'=>'已经存在最高级别机构不允许录入此层级机构'];
        }
        if($count && !$arr['o_pid']) {
            return ['res'=>false,'msg'=>'上级机构不可为空'];
        }
        $this->OrganizationModel->o_name = $arr['o_name'];
        $this->OrganizationModel->o_code = $this->createO_Code($count);//公司代码后三位+机构开设的顺序数字作为后三位
        $this->OrganizationModel->o_g_id = $arr['o_g_id'];
        $this->OrganizationModel->o_pid = $arr['o_pid'];
        $this->OrganizationModel->o_user = $arr['o_user'];
        $this->OrganizationModel->o_phone = $arr['o_phone'];
        $this->OrganizationModel->o_province = $arr['o_province'];
        $this->OrganizationModel->o_city = $arr['o_city']?$arr['o_city']:'';
        $this->OrganizationModel->o_area = $arr['o_area']?$arr['o_area']:'';
        $this->OrganizationModel->o_create = $arr['o_create'];
        $this->OrganizationModel->o_status = $arr['o_status'];
        $this->OrganizationModel->o_path = $this->getO_Path($arr['o_pid']);
        $this->OrganizationModel->o_depth = $this->getO_Depth($this->OrganizationModel->o_path);
        $res = $this->OrganizationModel->save();
        return ['res'=>true,'data'=>$res];
    }
    
    /**
     * 生成机构代码
     * @return string
     */
    private function createO_Code($count) {
        $len = strlen($this->SystemUser->c_code);
        if($len < 3) {
            $sysCode = $this->SystemUser->c_code;
            for ($i=1;$i<=3-$len;$i++){
                $sysCode .= '0';
            }
        }
        $len = strlen($count+1);
        if($len < 3) {
            $userCode = $count+1;
            for ($i=1;$i<=3-$len;$i++){
                $userCode .= '0';
            }
        }
        return substr($sysCode,-3).substr($userCode,-3);
    }
    
    /**
     * @param $o_pid
     * 生成机构路径
     * @return string
     */
    private function getO_Path($o_pid) {
        if($o_pid) {
            $p_organization = Organization::find($o_pid)->o_path;
            if($p_organization == ',') {
                return $o_pid.',';
            }
        }else{
            return ',';
        }
        
       
    }
    
    /**
     * @param $o_path
     * 获取机构路径深度
     * @return string
     */
    private function getO_Depth($o_pid) {
        if($o_pid) {
            $p_organization = Organization::find($o_pid)->o_depth;
            return $p_organization + 1;
        }else{
            return 1;
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/20
 * Time: 18:22
 */

namespace App\Bll\Common\Organization;

use App\Bll\Enum\OrganizationGradeFirstEnum;
use App\Model\LoginUser;
use App\Model\Organization;

class OrganizationGrade
{
    private $OrganizationGradeModel;
    
    /**
     * 注入一个机构等级数据库实例
     * OrganizationGrade constructor.
     */
    public function __construct() {
        $this->OrganizationGradeModel = new \App\Model\OrganizationGrade();
    }
    
    /**
     * @param $property
     * 实例操作
     * @return mixed
     */
    public function __get($property) {
        return $this->OrganizationGradeModel->$property;
    }
    
    /**
     * 实例操作
     * @param $property
     * @param $value
     */
    public function __set($property, $value) {
        $this->OrganizationGradeModel->$property = $value;
    }
    
    /**
     * 获取机构等级列表
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getList() {
        $list = $this->OrganizationGradeModel->orderBy('o_g_sort')->get();
        if(!count($list)) {
            $this->addDefault();
        }
        $list = $this->OrganizationGradeModel->orderBy('o_g_sort')->get();
        return $list;
    }
    
    /**
     * @param LoginUser $user
     * 获取小于等于当前登录用户机构等级的机构等级
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getLowLevelAndEqualListByLoginUser(LoginUser $user) {
        $o_g_id = Organization::getOrganizationsByCode($user->o_code)->o_g_id;
        $User_Grade = $this->OrganizationGradeModel->find($o_g_id);
        $list = $this->OrganizationGradeModel->where('o_g_sort','>=',$User_Grade->o_g_sort)->orderBy('o_g_sort')->get();
        return $list;
    }
    
    /**
     * @param LoginUser $user
     * 获取小于等于当前登录用户机构等级的机构等级id集合
     * @return array
     */
    public function getLowLevelAndEqualListIdsByLoginUser(LoginUser $user) {
        $o_g_id = Organization::getOrganizationsByCode($user->o_code)->o_g_id;
        $User_Grade = $this->OrganizationGradeModel->find($o_g_id);
        $list = $this->OrganizationGradeModel->where('o_g_sort','>=',$User_Grade->o_g_sort)->orderBy('o_g_sort')->get();
        $allow_o_g_ids = array_column($list->toArray(),'o_g_id');
        return $allow_o_g_ids;
    }
    
    /**
     * 增加默认总公司等级
     * @return bool
     */
    public function addDefault() {
        $this->OrganizationGradeModel->o_g_name = '总公司';
        $this->OrganizationGradeModel->o_g_sort = OrganizationGradeFirstEnum::总公司;
        $res = $this->OrganizationGradeModel->save();
        return $res;
    }
    
    /**
     * @param $arr
     * 新增机构等级信息
     * @return array
     */
    public function store($arr) {
        if(\App\Model\OrganizationGrade::getOrganizationGradesByName($arr['o_g_name'])->count()) {
            return ['res'=>false,'msg'=>'机构类型已存在'];
        }
        if(\App\Model\OrganizationGrade::getOrganizationGradesBySort($arr['o_g_sort'])->count()) {
            return ['res'=>false,'msg'=>'机构等级已存在'];
        }
        if($arr['o_g_sort'] != \App\Model\OrganizationGrade::count()+1) {
            return ['res'=>false,'msg'=>'机构等级输入有误'];
        }
        $this->OrganizationGradeModel->o_g_name = $arr['o_g_name'];
        $this->OrganizationGradeModel->o_g_sort = $arr['o_g_sort'];
        $res = $this->OrganizationGradeModel->save();
        return ['res'=>true,'data'=>$res];
    }
    
    /**
     * @param $arr
     * 新增机构等级信息
     * @return array
     */
    public function update($arr,$id) {
        if(\App\Model\OrganizationGrade::getOrganizationGradesByName($arr['o_g_name'])->count()) {
            return ['res'=>false,'msg'=>'机构类型已存在'];
        }
        $res = $this->OrganizationGradeModel->where('o_g_id',$id)->update(['o_g_name'=>$arr['o_g_name']]);
        return ['res' => $res?$res:false,'msg' => ''];
    }
    
    /**
     * @param $id
     * 删除机构类型信息
     * @return array
     */
    public function destroy($id) {
        $Grade = $this->OrganizationGradeModel->where('o_g_id',$id)->first();
        if(!$Grade) {
            return ['res'=>false,'msg'=>'未找到想要删除的机构类型信息'];
        }
        /**
         * 是否最低等级的机构等级
         */
        if($Grade->o_g_sort !== $this->getList()->count()) {
            return ['res'=>false,'msg'=>'只能删除最低等级机构类型'];
        }
        /**
         * 该等级下是否存在机构
         */
        if(Organization::getOrganizationsByGradeId($id)->count()) {
            return ['res'=>false,'msg'=>'该机构类型下存在机构，请先移除'];
        }
        $res = $this->OrganizationGradeModel->where('o_g_id',$id)->delete();
        return ['res' => $res?$res:false,'msg' => ''];
    }
    
    /**
     * @param $id
     * 根据id获取机构类型信息
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public static function getOrganizationGradeById($id) {
        return \App\Model\OrganizationGrade::find($id);
    }
    
    /**
     * 如果机构不是第一次录入则返回除去总公司的类型列表是第一次的话返回全部
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function showListForAddOrganization() {
        if(Organization::all()->count()){
            $list = $this->OrganizationGradeModel->orderBy('o_g_sort')->get();
            unset($list[0]);
        }else{
            $list = $this->OrganizationGradeModel->orderBy('o_g_sort')->get();
        }
        return $list;
    }
}
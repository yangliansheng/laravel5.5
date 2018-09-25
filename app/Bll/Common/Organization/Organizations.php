<?php
/**
 * Created by PhpStorm.
 * User: yls
 * Date: 2018/9/21
 * Time: 15:22
 */

namespace App\Bll\Common\Organization;

use App\Model\Organization;

class Organizations
{
    private $OrganizationModel;
    
    /**
     * 注入一个机构等级数据库实例
     * OrganizationGrade constructor.
     */
    public function __construct() {
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
     * 获取机构等级列表
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getList() {
        $list = $this->OrganizationModel->orderBy('o_g_sort')->get();
        if(!count($list)) {
            $this->addDefault();
        }
        $list = $this->OrganizationModel->orderBy('o_g_sort')->get();
        return $list;
    }
    
    /**
     * 增加默认总公司等级
     * @return bool
     */
    public function addDefault() {
        $this->OrganizationModel->o_g_name = '总公司';
        $this->OrganizationModel->o_g_sort = 1;
        $res = $this->OrganizationModel->save();
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
        $this->OrganizationModel->o_g_name = $arr['o_g_name'];
        $this->OrganizationModel->o_g_sort = $arr['o_g_sort'];
        $res = $this->OrganizationModel->save();
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
        $res = $this->OrganizationModel->where('o_g_id',$id)->update(['o_g_name'=>$arr['o_g_name']]);
        return ['res' => $res?$res:false,'msg' => ''];
    }
    
    /**
     * @param $id
     * 删除机构类型信息
     * @return array
     */
    public function destroy($id) {
        $Grade = $this->OrganizationModel->where('o_g_id',$id)->first();
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
        $res = $this->OrganizationModel->where('o_g_id',$id)->delete();
        return ['res' => $res?$res:false,'msg' => ''];
    }
}
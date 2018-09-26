<?php

namespace App\Http\Controllers\Common;

use App\Bll\Common\Account\AccountManage;
use App\Bll\Common\Organization\OrganizationGrade;
use App\Bll\Enum\LogTypeEnum;
use App\Model\OrganizationGradeLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrganizationGradeController extends Controller
{
    
    /**
     * Display a listing of the resource.
     * 获取机构等级列表
     * 如果登录用户不是超管身份则只能查看本类型及等级之下的类型
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->getIsAdminer();
        $grade = new OrganizationGrade();
        if($this->isAdminer) {
            $list = $grade->getList($this->LoginUser);
        }else{
            $list = $grade->getLowLevelAndEqualListByLoginUser($this->LoginUser);
        }
        return $this->response()->success($list);
    }
    
    /**
     * 获取小于等于当前登录用户机构等级的机构等级
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function showList() {
        $grade = new OrganizationGrade();
        $this->bindingUser();
        $res = $grade->getLowLevelAndEqualListByLoginUser($this->LoginUser);
        return $this->response()->success($res);
    }
    
    /**
     * 给录入机构使用的机构类型list接口
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function showListForAddOrganization() {
        $grade = new OrganizationGrade();
        $list = $grade->showListForAddOrganization();
        return $this->response()->success($list);
    }
    
    /**
     * Store a newly created resource in storage.
     * 新增机构类型
     * 超管权限
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->isAllowAction();
        $validate = \Validator::make($request->all(), [
            'o_g_name' => 'required|string|max:10',
            'o_g_sort' => 'required|integer|',
        ],[
            'o_g_name.required' => '机构类型名称不能为空',
            'o_g_name.max' => '机构类型名称不能超过十个字符',
            'o_g_sort.required' => '机构等级不能为空',
        ]);
        if($validate->fails())
        {
            $msg = implode(',',$validate->errors()->all());
            return $this->response()->error($msg,-200);
        }
        $Grade = new OrganizationGrade();
        $res = $Grade->store($request->all());
        if($res['res']) {
            //日志记录
            OrganizationGradeLog::addnew([
                'type' => LogTypeEnum::新增,
                'describe'=> 'o_g_name:'.$request->o_g_name.',o_g_sort:'.$request->o_g_sort,
                'adminer'=> $this->LoginUser->u_id
            ]);
            return $this->response()->success('新增完成');
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }

    /**
     * Display the specified resource.
     * 获取一个机构类型的信息
     * 是超管或者在小于等于当前登录用户机构等级的机构等级id集合内
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->getIsAdminer();
        $grade = new OrganizationGrade();
        $ids = $grade->getLowLevelAndEqualListIdsByLoginUser($this->LoginUser);
        if($this->isAdminer || in_array($id,$ids)) {
            $Grade = new \App\Model\OrganizationGrade();
            $res = $Grade->where('o_g_id',$id)->first();
            if($res) {
                return $this->response()->success($res);
            }else{
                return $this->response()->error('未找到该机构类型信息',-200);
            }
        }else{
            return $this->response()->error(config('exception_code.-102'),-102);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * 更新机构类型信息
     * 超管权限才能调用
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->isAllowAction();
        $validate = \Validator::make($request->all(), [
            'o_g_name' => 'required|string|max:10',
        ],[
            'o_g_name.required' => '机构类型名称不能为空',
            'o_g_name.max' => '机构类型名称不能超过十个字符',
        ]);
        if($validate->fails())
        {
            $msg = implode(',',$validate->errors()->all());
            return $this->response()->error($msg,-200);
        }
        $Grade = new OrganizationGrade();
        $res = $Grade->update($request->all(),$id);
        if($res['res']) {
            //日志记录
            OrganizationGradeLog::addnew([
                'type' => LogTypeEnum::修改,
                'describe'=> 'o_g_id:'.$id.',o_g_name:'.$Grade->getOrganizationGradeById($id)->o_g_name.',new_o_g_name:'.$request->o_g_name,
                'adminer'=> $this->LoginUser->u_id
            ]);
            return $this->response()->success('修改完成');
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 删除机构等级信息
     * 是超管
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->isAllowAction();
        $Grade = new OrganizationGrade();
        $res = $Grade->destroy($id);
        if($res['res']) {
            //日志记录
            OrganizationGradeLog::addnew([
                'type' => LogTypeEnum::删除,
                'describe'=> 'o_g_id:'.$id,
                'adminer'=> $this->LoginUser->u_id
            ]);
            return $this->response()->success('删除成功');
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }
    
    /**
     * 是否允许运行接口
     * @return \App\Http\Controllers\引发一个http请求的错误异常
     */
    private function isAllowAction() {
        $this->getIsAdminer();
        if(!$this->isAdminer) {
            return $this->response()->error(config('exception_code.-102'),-102);
        }
    }
}

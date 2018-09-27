<?php

namespace App\Http\Controllers\Common;

use App\Bll\Common\Organization\Organizations;
use App\Bll\Common\Team\Team;
use App\Bll\Enum\AgentStatusEnum;
use App\Bll\Enum\TeamGradeEnum;
use App\Bll\Enum\TeamStatusEnum;
use App\Model\Agent;
use App\Model\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     * 查询组织列表
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validate = \Validator::make($request->all(), [
            'o_id' => 'present|integer|nullable',
            't_name' => 'present|string|nullable',
            't_code' => 'present|string|nullable',
            't_status' => 'present|integer|nullable|in:'.TeamStatusEnum::组织状态_无效.','.TeamStatusEnum::组织状态_有效.','.TeamStatusEnum::组织状态_迁移中.'',
            't_grade' => 'present|integer|nullable|in:'.TeamGradeEnum::区.','.TeamGradeEnum::部.'',
            'ag_name' => 'present|string|nullable',
            't_pname' => 'present|string|nullable',
        ],[
            'o_id.present' => '选择机构必传可以为空',
            't_name.present' => '组织名称必传可以为空',
            't_code.present' => '组织代码必传可以为空',
            't_status.present' => '组织状态必传可以为空',
            'ag_name.present' => '组织级别必传可以为空',
            't_pname.present' => '上级组织名称必传可以为空',
            't_status.in' => '错误的组织状态',
            't_grade.in' => '错误的组织级别',
        ]);
        if($validate->fails()) {
            $msg = implode(',',$validate->errors()->all());
            return $this->response()->error($msg,-200);
        }
        $this->getIsAdminer();
        $Team = new Team($this->LoginUser,$this->AdminUser);
        $res = $Team->index($request->all(),$this->isAdminer);
        if($res['res']) {
            foreach ($res['data'] as &$value){
                $value->o_name = Organization::find($value->o_id)->o_name;
                $value->t_pname = $value->t_pid?\App\Model\Team::find($value->t_pid)->t_name:'';
                $value->ag_count = Agent::where('t_id',$value->t_id)->whereNotIn('ag_status',[AgentStatusEnum::离司,AgentStatusEnum::待入司])->get()->count();
            }
            return $this->response()->success($res['data']);
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!isset($request->type)|| !in_array($request->type,[TeamGradeEnum::部,TeamGradeEnum::区])) {
            return $this->response()->error('请输入正确的新增的组织级别',-200);
        }
        switch ($request->type) {
            case TeamGradeEnum::区: {
                $rules = [
                    'o_g_name' => 'required|string|max:10',
                    'o_g_sort' => 'required|integer|',
                ];
                $messages = [
                    'o_g_name.required' => '机构类型名称不能为空',
                    'o_g_name.max' => '机构类型名称不能超过十个字符',
                    'o_g_sort.required' => '机构等级不能为空',
                ];
                $funtionName = 'storeQu';
                break;
            }
            case TeamGradeEnum::部: {
                $rules = [
                    'o_g_name' => 'required|string|max:10',
                    'o_g_sort' => 'required|integer|',
                ];
                $messages = [
                    'o_g_name.required' => '机构类型名称不能为空',
                    'o_g_name.max' => '机构类型名称不能超过十个字符',
                    'o_g_sort.required' => '机构等级不能为空',
                ];
                $funtionName = 'storeBu';
                break;
            }
            default:{
                return $this->response()->error('请输入正确的新增的组织级别',-200);
            }
        }
        $this->isAllowAction();
        $validate = \Validator::make($request->all(), $rules,$messages);
        if($validate->fails())
        {
            $msg = implode(',',$validate->errors()->all());
            return $this->response()->error($msg,-200);
        }
        $Team = new Team($this->LoginUser,$this->AdminUser);
        $res = $Team->$funtionName($request->all(),$this->isAdminer);
        if($res['res']) {
            return $this->response()->success('新增完成');
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }
    
    /**
     * @param Request $request
     * 获取根据组织名获取可管理的组织列表
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function showList(Request $request) {
        $this->getIsAdminer();
        $Team = new Team($this->LoginUser,$this->AdminUser);
        $res = $Team->getAllAllowTeamsByLoginUser($this->isAdminer,$request->all());
        return $this->response()->success($res);
    }

    /**
     * Display the specified resource.
     * 获取组织详情
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->getIsAdminer();
        $Teams = new Team($this->LoginUser,$this->AdminUser);
        $res = $Teams->show($id,$this->isAdminer);
        if($res['res']) {
            return $this->response()->success($res['data']);
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->getIsAdminer();
        $Teams = new Team($this->LoginUser,$this->AdminUser);
        $res = $Teams->destroy($id,$this->isAdminer);
        if($res['res']) {
            return $this->response()->success('注销成功');
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }
}

<?php

namespace App\Http\Controllers\Common;

use App\Bll\Common\Organization\OrganizationGrade;
use App\Bll\Common\Organization\Organizations;
use App\Bll\Enum\LogTypeEnum;
use App\Bll\Enum\OrganizationStatus;
use App\Model\Agent;
use App\Model\Organization;
use App\Model\OrganizationLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrganizationController extends Controller
{
    
    /**
     * Display a listing of the resource.
     * 获取机构信息列表
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validate = \Validator::make($request->all(), [
            'o_name' => 'present|string|nullable',
            'o_g_id' => 'present|integer|nullable',
            'o_status' => 'present|integer|nullable',
        ],[
            'o_name.present' => '机构名称必传可以为空',
            'o_g_id.present' => '机构类型必传可以为空',
            'o_status.present' => '机构状态必传可以为空',
            'o_name.string' => '错误的机构名称',
            'o_g_id.integer' => '错误的机构类型',
            'o_status.integer' => '错误的机构状态',
        ]);
        if($validate->fails()) {
            $msg = implode(',',$validate->errors()->all());
            return $this->response()->error($msg,-200);
        }
        $this->getIsAdminer();
        $grade = new Organizations($this->LoginUser);
        $res = $grade->getList($request->all(),$this->isAdminer);
        if($res['res']) {
            foreach ($res['data'] as &$value){
                $value->o_g_name = OrganizationGrade::getOrganizationGradeById($value->o_g_id)->o_g_name;
                $value->o_p_name = $value->o_pid?Organization::find($value->o_pid)->o_name:'无';
                $value->agent_num = Agent::where('o_id',$value->o_id)->count();
            }
            return $this->response()->success($res['data']);
        }else{
            return $this->response()->error($res['msg'],-200);
        }
        
    }
    
    /**
     * 获取上级机构列表(获取所有的未关停的机构信息)
     * @return \App\Http\Controllers\引发一个http请求的错误异常|\App\Http\Controllers\返回一个response的对像
     */
    public function showUpList(Request $request) {
        $this->bindingUser();
        $grade = new Organizations($this->LoginUser);
        $res = $grade->getAllNotClose($request->all());
        if($res['res']) {
            return $this->response()->success($res['data']);
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }
    /**
     * Store a newly created resource in storage.
     * 新增机构
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->getIsAdminer();
        if(!$this->isAdminer) {
            return $this->response()->error(config('exception_code.-102'),-102);
        }
        $validate = \Validator::make($request->all(), [
            'o_name' => 'required|string|max:50',
            'o_g_id' => 'required|integer',
            'o_pid' => 'present|integer|nullable',
            'o_user' => 'required|string|max:10',
            'o_province' => 'required|string',
            'o_city' => 'present|string|nullable',
            'o_area' => 'present|string|nullable',
            'o_create' => 'required|string|date',
            'o_phone' => ['required','regex:/^((0\d{2,3}-?)?\d{7,8})|(1[3-9]\d{9})$/'],
            'o_status'=>'required|in:'.OrganizationStatus::关停.','.OrganizationStatus::营业.''
        ],[
            'o_name.required' => '机构名称不能为空',
            'o_user.max' => '机构名称不能超过50个字符',
            'o_g_id.required' => '机构类型不能为空',
            'o_pid.required' => '上级机构不能为空',
            'o_province.required' => '地区不能为空',
            'o_city.required' => '地区不能为空',
            'o_area.required' => '地区不能为空',
            'o_create.required' => '成立日期不能为空',
            'o_create.date' => '成立日期格式不对',
            'o_user.required' => '机构负责人不能为空',
            'o_user.max' => '机构负责人名称不能超过十个字符',
            'o_phone.required' => '机构负责人联系电话不能为空',
            'o_user.regex' => '机构负责人联系电话格式不正确',
            'o_status.required' => '机构状态不能为空',
            'o_status.in' => '机构状态错误',
    
        ]);
        if($validate->fails())
        {
            $msg = implode(',',$validate->errors()->all());
            return $this->response()->error($msg,-200);
        }
        $Organizations = new Organizations($this->LoginUser,$this->AdminUser);
        $res = $Organizations->store($request->all());
        if($res['res']) {
            //日志记录
            OrganizationLog::addnew([
                'type' => LogTypeEnum::新增,
                'describe'=> json_encode($request->all()),
                'adminer'=> $this->LoginUser->u_id
            ]);
            return $this->response()->success('新增完成');
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }

    /**
     * Display the specified resource.
     * 获取机构信息
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->getIsAdminer();
        $Organizations = new Organizations($this->LoginUser);
        if($this->isAdminer || in_array($id,$Organizations->getAllowShowOrganizations($this->isAdminer))) {
            $data = Organization::find($id);
            return $this->response()->success($data?$data:[]);
        }else{
            return $this->response()->error(config('exception_code.-102'),-102);
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
        $validate = \Validator::make($request->all(), [
            'o_user' => 'required|string|max:10',
            'o_phone' => ['required','regex:/^((0\d{2,3}-?)?\d{7,8})|(1[3-9]\d{9})$/'],
            'o_status'=>'required|in:'.OrganizationStatus::关停.','.OrganizationStatus::营业.''
        ],[
            'o_user.required' => '机构负责人不能为空',
            'o_user.max' => '机构负责人名称不能超过十个字符',
            'o_phone.required' => '机构负责人联系电话不能为空',
            'o_user.regex' => '机构负责人联系电话格式不正确',
            'o_status.required' => '机构状态不能为空',
            'o_status.in' => '机构状态错误',
            
        ]);
        if($validate->fails())
        {
            $msg = implode(',',$validate->errors()->all());
            return $this->response()->error($msg,-200);
        }
        $this->getIsAdminer();
        $Organizations = new Organizations($this->LoginUser);
        $res = $Organizations->update($request->all(),$id,$this->isAdminer);
        if($res['res']) {
            //日志记录
            $describe = 'o_g_id:'.$id.',Organization:'.json_encode(Organization::find($id)->toArray()).',newOrganization:'.json_encode($request->all());
            if($request->o_status == OrganizationStatus::关停) {
                OrganizationLog::addnew([
                    'type' => LogTypeEnum::关停,
                    'describe'=>$describe,
                    'adminer'=> $this->LoginUser->u_id
                ]);
            }
            if($request->o_status == OrganizationStatus::营业) {
                OrganizationLog::addnew([
                    'type' => LogTypeEnum::营业,
                    'describe'=>$describe,
                    'adminer'=> $this->LoginUser->u_id
                ]);
            }
            OrganizationLog::addnew([
                'type' => LogTypeEnum::修改,
                'describe'=> $describe,
                'adminer'=> $this->LoginUser->u_id
            ]);
            return $this->response()->success('修改完成');
        }else{
            return $this->response()->error($res['msg'],-200);
        }
       
    }

    /**
     * Remove the specified resource from storage.
     * 无删除操作
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

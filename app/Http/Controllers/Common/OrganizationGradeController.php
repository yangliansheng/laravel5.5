<?php

namespace App\Http\Controllers\Common;

use App\Bll\Common\Organization\OrganizationGrade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class OrganizationGradeController extends Controller
{
    /**
     * Display a listing of the resource.
     * 获取机构等级列表
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $grade = new OrganizationGrade();
        $res = $grade->getList();
        return $this->response()->success($res);
    }
    
    /**
     * Store a newly created resource in storage.
     * 新增机构类型
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
            return $this->response()->success('新增完成');
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }

    /**
     * Display the specified resource.
     * 获取一个机构类型的信息
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Grade = new \App\Model\OrganizationGrade();
        $res = $Grade->where('o_g_id',$id)->first();
        if($res) {
            return $this->response()->success($res);
        }else{
            return $this->response()->error('未找到该机构类型信息',-200);
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
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
            return $this->response()->success('修改完成');
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 删除机构等级信息
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Grade = new OrganizationGrade();
        $res = $Grade->destroy($id);
        if($res['res']) {
            return $this->response()->success('删除成功');
        }else{
            return $this->response()->error($res['msg'],-200);
        }
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/18
 * Time: 9:42
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Company as MdlCompany;
use App\Bll\API\Company as BllCompany;


class CompanyController extends Controller{
    
    /**
     * 保险公司列表 带搜索条件和分页
     *
     * @param Request $request
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function list(Request $request){
        $search = [];

        if($request->__isset('search_name') && trim($request->search_name) !== '')
            $search['c_name'] = $request->search_name;

        if($request->__isset('search_short_name') && trim($request->search_short_name) !== '')
            $search['c_short_name'] = $request->search_short_name;

        if($request->__isset('search_code') && trim($request->search_code) !== '')
            $search['c_code'] = $request->search_code;

        if($request->__isset('search_status') && trim($request->search_status) !== '')
            $search['c_status'] = (int)$request->search_status;

        $perPage = $request->perPage ? $request->perPage : 2;
        $page = $request->page ? $request->page : 1;
        $data = BllCompany::getList($search, $perPage, $page);
        
        //追加额外参数，例如搜索条件
        $appendData = array(
            'search_name' => !isset($search['c_name']) ? '' : $search['c_name'],
            'search_short_name' => !isset($search['c_short_name']) ? '' : $search['c_short_name'],
            'search_code' => !isset($search['c_code']) ? '' : $search['c_code'],
            'search_status' => isset($search['c_status']) ? $search['c_status'] : '',
            'perPage' => $perPage,
            'page' => $page,
        );

        $return['listData'] = $data;
        $return['paramsData'] = $appendData;

        return $this->response()->success($return);
    }
    
    /**
     * 获取全部保险公司列表
     *
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function index(){
        $data = MdlCompany::all();
        return $this->response()->success($data);
    }

    /**
     * 跳转到添加页面
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(){
        return view('company/edit');
    }
    
    /**
     * 保存保险公司信息
     *
     * @param Request $request
     * @return \App\Http\Controllers\引发一个http请求的错误异常|\App\Http\Controllers\返回一个response的对像|\App\Http\Controllers\返回错误异常
     */
    public function store(Request $request){
        try{
            $this->validate($request, [
                'c_name' => 'required|max:50',
                'c_short_name' => 'required|max:50',
                'c_code' => 'required|unique:c_insurance_company,c_code',
                'c_status' => 'required|integer',
                'c_start' => 'date',
                'c_end' => 'date'
            ]);
        }catch (\Exception $exception){
            return $this->response()->error('参数错误', -200);
        }

        $company = new MdlCompany();
        $company->c_name = $request->c_name;
        $company->c_short_name = $request->c_short_name;
        $company->c_code = $request->c_code;
        $company->c_status = $request->c_status;
        
        if($request->__isset('c_start'))
            $company->c_start = $request->c_start;
        
        if($request->__isset('c_end'))
            $company->c_end = $request->c_end;

        if($request->__isset('c_tel')){
            if(!empty($request->c_tel) && !preg_match('/^1[3|4|5|7|8]\d{9}$/', $request->c_tel)){
                return $this->response()->error('手机号码格式错误', -200);
            }

            $company->c_tel = $request->c_tel;
        }

        $company->add_time = date('Y-m-d H:i:s');
        $company->update_time = date('Y-m-d H:i:s');

        try{
            $rs = $company->save();
            return $this->response()->success('添加成功');
        }catch (\Exception $exception){
            return $this->response()->responseException($exception);
        }
    }
    
    /**
     * 显示指定保险公司详情信息
     *
     * @param $id
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function show($id){
        $c = new MdlCompany();
        $res = $c->findOne(intval($id));
        
        if(empty($res))
            return $this->response()->error('无数据', -200);
        else
            return $this->response()->success($res);
    }

    /**
     * 跳到保险公司编辑页面
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id){
        return view('company/edit', MdlCompany::find($id));
    }
    
    /**
     * 修改保险公司信息
     *
     * @param Request $request
     * @param $id
     * @return \App\Http\Controllers\返回一个response的对像|\App\Http\Controllers\返回错误异常
     */
    public function update(Request $request, $id){
        try{
            $this->validate($request, [
                'c_name' => 'required|max:50',
                'c_short_name' => 'required|max:50',
                'c_code' => 'required|unique:c_insurance_company,c_code,'.$id.',c_id',
                'c_status' => 'required|integer',
                'c_start' => 'date',
                'c_end' => 'date'
            ]);
        }catch (\Exception $exception){
            return $this->response()->error('参数错误', -200);
        }

        $company = new MdlCompany();

        if($request->__isset('c_name')){
            $data['c_name'] = $request->c_name;
        }

        if($request->__isset('c_short_name')){
            $data['c_short_name'] = $request->c_short_name;
        }

        if($request->__isset('c_code')){
            $data['c_code'] = $request->c_code;
        }

        if($request->__isset('c_tel')){
            if(!empty($request->c_tel) && !preg_match('/^1[3|4|5|7|8]\d{9}$/', $request->c_tel)){
                return $this->response()->error('手机号码格式错误', -200);
            }

            $data['c_tel'] = $request->c_tel;
        }

        if($request->__isset('c_status')){
            $data['c_status'] = $request->c_status;
        }

        if($request->__isset('c_start')){
            $data['c_start'] = $request->c_start;
        }

        if($request->__isset('c_end')){
            $data['c_end'] = $request->c_end;
        }

        $data['update_time'] = date('Y-m-d H:i:s');

        try{
            $rs = $company->where("c_id", $id)->update($data);
            return $this->response()->success('编辑成功');
        }catch (\Exception $exception){
            return $this->response()->responseException($exception);
        }
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \App\Http\Controllers\返回一个response的对像|\App\Http\Controllers\返回错误异常
     */
    public function destroy($id)
    {
        //删除
        try{
            MdlCompany::destroy($id);
            return $this->response()->success('删除成功');
        }catch(\Exception $exception) {
            return $this->response()->responseException($exception);
        }
    }
    
    /**
     * 根据保险公司代码检查保险公司是否已存在
     *
     * @param Request $request
     * @return \App\Http\Controllers\引发一个http请求的错误异常|\App\Http\Controllers\返回一个response的对像
     */
    public function checkCode(Request $request){
        if($request->__isset('c_id') && intval($request->c_id) > 0){
            $c_id = intval($request->c_id);
        }else{
            $c_id = 0;
        }
        
        if(!empty($request->c_code)){
            $bll_company = new BllCompany();
            $res = $bll_company->codeIsExist($request->c_code, $c_id);
            
            if($res){
                return $this->response()->error('保险公司已存在', -200);
            }
        }
        
        return $this->response()->success('可使用');
    }
    
    /**
     * 获取系统提供的所有保险公司列表
     *
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function sysCompList(){
        $list = config('insurance_company');
        return $this->response()->success($list);
    }
    
    /**
     * 根据系统保险公司id获取系统保险公司详细信息
     *
     * @param $ic_id
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function sysCompInfo($ic_id){
        $info = config('insurance_company.'.(intval($ic_id)-1));
        return $this->response()->success($info);
    }
}
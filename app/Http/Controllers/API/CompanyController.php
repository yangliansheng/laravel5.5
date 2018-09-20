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
use App\Model\Company;
use App\Bll\API\Company as BllCompany;


class CompanyController extends Controller{
    
    /**
     * 获取全部保险公司列表
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(){
        $data = Company::all();
        return $this->response($data,0, '全部保险公司列表');
    }
    
    /**
     * 保险公司列表 带搜索条件和分页
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request){
        $search = [];

        if($request->__isset('search_name'))
            $search['c_name'] = $request->search_name;

        if($request->__isset('search_short_name'))
            $search['c_short_name'] = $request->search_short_name;

        if($request->__isset('search_code'))
            $search['c_code'] = $request->search_code;

        if($request->__isset('search_status'))
            $search['c_status'] = (int)$request->search_status;

        $perPage = $request->perPage ? $request->perPage : 2;
        $page = $request->page ? $request->page : 1;
        $data = BllCompany::getList($search, $perPage, $page);

//        $data = Company::select(['c_id', 'c_name', 'c_short_name', 'c_code', 'c_tel', 'c_status', 'c_start', 'c_end', 'add_time', 'update_time'])
//            ->where(function ($query) use ($search) {
//                if (isset($search['c_name']) && !empty($search['c_name'])) {
//                    $query->where('c_name', 'like', '%' . $search['c_name'] . '%');
//                }
//            })
//            ->where(function ($query) use ($search) {
//                if (isset($search['c_short_name']) && !empty($search['c_short_name'])) {
//                    $query->where('c_short_name', 'like', '%' . $search['c_short_name'] . '%');
//                }
//            })
//            ->where(function ($query) use ($search) {
//                if (isset($search['c_code']) && !empty($search['c_code'])) {
//                    $query->where('c_code', 'like', '%' . $search['c_code'] . '%');
//                }
//            })
//            ->where(function ($query) use ($search) {
//                if (isset($search['c_status'])) {
//                    $query->where('c_status', '=', $search['c_status']);
//                }
//            })
//            ->orderBy('c_id', 'desc')
//            ->paginate($perPage);

        //追加额外参数，例如搜索条件
        $appendData = array(
            'search_name' => empty($search['c_name']) ? '' : $search['c_name'],
            'search_short_name' => empty($search['c_short_name']) ? '' : $search['c_short_name'],
            'search_code' => empty($search['c_code']) ? '' : $search['c_code'],
            'search_status' => isset($search['c_status']) ? $search['c_status'] : '',
            'perPage' => $perPage,
        );

        $return['data'] = $data;
        $return['paramsData'] = $appendData;

        return $this->response($return,0, '保险公司列表');
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
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request){
        try{
            $this->validate($request, [
                'c_name' => 'required|max:50',
                'c_short_name' => 'required|max:50',
                'c_code' => 'required|unique:c_insurance_company,c_code',
            ]);
        }catch (\Exception $exception){
            return $this->response([],1, '参数错误');
        }

        $company = new Company();
        $company->c_name = $request->c_name;
        $company->c_short_name = $request->c_short_name;
        $company->c_code = $request->c_code;

        if($request->__isset('c_tel')){
            if(!empty($request->c_tel) && !preg_match('/^1[3|4|5|7|8]\d{9}$/', $request->c_tel)){
                return $this->response([],1, '手机号码格式错误');
            }

            $company->c_tel = $request->c_tel;
        }

        $company->add_time = date('Y-m-d H:i:s');
        $company->update_time = date('Y-m-d H:i:s');

        try{
            $rs = $company->save();
            return $this->response($rs,0,'保存成功');
        }catch (\Exception $exception){
            return $this->response([],1, $exception->getMessage());
        }
    }
    
    /**
     * 显示指定保险公司详情信息
     *
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function show($id){
        $c = new Company();
        $res = $c->findOne(intval($id));
        return $this->response($res,0,'保险公司详情');
    }

    /**
     * 跳到保险公司编辑页面
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id){
        return view('company/edit', Company::find($id));
    }

    /**
     * 修改保险公司信息
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request, $id){
        try{
            $this->validate($request, [
                'c_name' => 'required|max:50',
                'c_short_name' => 'required|max:50',
                'c_code' => 'required|unique:c_insurance_company,c_code,'.$id.',c_id',
            ]);
        }catch (\Exception $exception){
            return $this->response([],1, '参数错误');
        }

        $company = new Company();

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
                return $this->response([],1, '手机号码格式错误');
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
            return $this->response($rs,0,'修改成功');
        }catch (\Exception $exception){
            return $this->response([],1, $exception->getMessage());
        }
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Test  $test
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //删除
        try{
            Company::destroy($id);
            return $this->response([],0,'删除成功');
        }catch(\Exception $exception) {
            return $this->response([],1,$exception->getMessage());
        }
    }

    /**
     * 根据保险公司代码检查保险公司是否已存在
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function checkCode(Request $request){
        try{
            $this->validate($request, [
                'c_code' => 'required|unique:c_insurance_company,c_code',
            ]);

            return $this->response([],0, '可使用');
        }catch (\Exception $exception){
            return $this->response([],1, '保险公司已存在');
        }
    }

    /**
     * 获取系统提供的所有保险公司列表
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function sysCompList(){
        $list = config('insurance_company');
        return $this->response($list,0, '系统保险公司列表');
    }

    /**
     * 根据系统保险公司id获取系统保险公司详细信息
     *
     * @param $ic_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function sysCompInfo($ic_id){
        $info = config('insurance_company.'.(intval($ic_id)-1));
        return $this->response($info,0, '系统保险公司详情');
    }
}
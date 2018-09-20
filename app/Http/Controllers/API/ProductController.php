<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/19
 * Time: 11:48
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Product as MdlProduct;

class ProductController extends Controller{
    
    /**
     * 获取保险产品列表 带条件搜索和分页
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function list(Request $request){
        $search = [];

        if($request->__isset('search_cid'))
            $search['c_id'] = intval($request->search_cid);

        if($request->__isset('search_name'))
            $search['p_name'] = trim($request->search_name);

        if($request->__isset('search_property'))
            $search['p_property'] = intval($request->search_property);

        if($request->__isset('search_status'))
            $search['p_status'] = intval($request->search_status);

        $perPage = $request->perPage ? $request->perPage : 10;
        $page = $request->page ? $request->page : 1;

        $data = MdlProduct::select(['*'])
            ->where(function ($query) use ($search) {
                if (isset($search['c_id']) && !empty($search['c_id'])) {
                    $query->where('c_id', '=', $search['c_id']);
                }
            })
            ->where(function ($query) use ($search) {
                if (isset($search['p_name']) && !empty($search['p_name'])) {
                    $query->where('p_name', 'like', '%' . $search['p_name'] . '%');
                }
            })
            ->where(function ($query) use ($search) {
                if (isset($search['p_property'])) {
                    $query->where('p_property', '=', $search['p_property']);
                }
            })
            ->where(function ($query) use ($search) {
                if (isset($search['p_status'])) {
                    $query->where('p_status', '=', $search['p_status']);
                }
            })
            ->orderBy('p_id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        //追加额外参数，例如搜索条件
        $appendData = array(
            'search_cid' => empty($search['c_id']) ? '' : $search['c_id'],
            'search_name' => empty($search['p_name']) ? '' : $search['p_name'],
            'search_property' => !isset($search['p_property']) ? '' : $search['p_property'],
            'search_status' => isset($search['p_status']) ? $search['p_status'] : '',
            'perPage' => $perPage,
        );

        $return['data'] = $data;
        $return['paramsData'] = $appendData;

        return $this->response($return,0, '保险产品列表');
    }
    
    /**
     * 获取全部保险产品列表
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(){
        $data = MdlProduct::all();
        return $this->response($data,0, '全部保险产品列表');
    }
    
    public function create(){
        //跳到添加view
        return view('product.add');
    }
    
    /**
     * 添加产品
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request){
        try{
            $this->validate($request, [
                'c_id' => 'required|numeric',
                'p_name' => 'required|max:150',
                'p_code' => 'required|unique:c_product,p_code',
                'p_pay_time' => 'required|numeric',
            ]);
        }catch (\Exception $exception){
            return $this->response([],1, '参数错误');
        }
    
        $product = new MdlProduct();
        $product->c_id = intval($request->c_id);
        $product->p_name = trim($request->p_name);
        $product->p_short_name = empty($request->p_short_name) ? '' : trim($request->p_short_name);
        $product->p_code = trim($request->p_code);
        $product->p_start_age = intval($request->p_start_age);
        $product->p_end_age = intval($request->p_end_age);
        $product->p_type = intval($request->p_type);
        $product->p_property = intval($request->p_property);
        $product->p_status = intval($request->p_status);
        $product->p_bao_type = intval($request->p_bao_type);
        $product->p_bao_time = intval($request->p_bao_time);
        $product->p_pay_type = intval($request->p_pay_type);
        $product->p_pay_time = intval($request->p_pay_time);
        $product->p_pay_way = intval($request->p_pay_way);
        $product->p_duty = empty($request->p_duty) ? '' : $request->p_duty;
        $product->p_rule = empty($request->p_rule) ? '' : $request->p_rule;
        $product->p_disclaimer = empty($request->p_disclaimer) ? '' : $request->p_disclaimer;
        $product->p_detail = empty($request->p_detail) ? '' : $request->p_detail;
        $product->p_disease = empty($request->p_disease) ? '' : $request->p_disease;
        $product->p_insurance = empty($request->p_insurance) ? '' : $request->p_insurance;
        $product->add_time = date('Y-m-d H:i:s');
        $product->update_time = date('Y-m-d H:i:s');
        
        try{
            $rs = $product->save();
            return $this->response($rs,0,'添加成功');
        }catch (\Exception $exception){
            return $this->response([],1, $exception->getMessage());
        }
    }
    
    /**
     * 显示指定保险产品详情信息
     *
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function show($id){
        $p = new MdlProduct();
        $res = $p->findOne(intval($id));
        return $this->response($res,0,'保险产品详情');
    }
    
    public function edit($id){
        //跳到编辑view
        $prod = new MdlProduct();
        $data = $prod->findOne($id);
        return view('product.add', ['data' => $data]);
    }
    
    /**
     * 编辑产品
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request, $id){
        try{
            $this->validate($request, [
                'c_id' => 'required|numeric',
                'p_name' => 'required|max:150',
                'p_code' => 'required|unique:c_product,p_code,' . $id . ',p_id',
                'p_pay_time' => 'required|numeric',
            ]);
        }catch (\Exception $exception){
            return $this->response([],1, '参数错误');
        }
        
        $data['c_id'] = intval($request->c_id);
        $data['p_name'] = trim($request->p_name);
        $data['p_code'] = trim($request->p_code);
        $data['p_status'] = intval($request->p_status);
        $data['p_type'] = intval($request->p_type);
        $data['p_bao_type'] = intval($request->p_bao_type);
        $data['p_bao_time'] = intval($request->p_bao_time);
        $data['p_pay_type'] = intval($request->p_pay_type);
        $data['p_pay_time'] = intval($request->p_pay_time);
        $data['p_pay_way'] = intval($request->p_pay_way);
    
        if($request->__isset('c_short_name')){
            $data['c_short_name'] = $request->c_short_name;
        }
    
        if($request->__isset('p_property')){
            $data['p_property'] = intval($request->p_property);
        }
    
        if($request->__isset('p_start_age')){
            $data['p_start_age'] = intval($request->p_start_age);
        }
    
        if($request->__isset('p_end_age')){
            $data['p_end_age'] = intval($request->p_end_age);
        }
        
        if($data['p_bao_type'] && $data['p_bao_time'] <= 0)
            return $this->response([],1, '请输入保险期间');
    
        if($data['p_pay_type'] <= 0 || $data['p_pay_time'] <= 0)
            return $this->response([],1, '请输入缴费期间');
    
        if($request->__isset('p_duty')){
            $data['p_duty'] = $request->p_duty;
        }
    
        if($request->__isset('p_rule')){
            $data['p_rule'] = $request->p_rule;
        }
    
        if($request->__isset('p_disclaimer')){
            $data['p_disclaimer'] = $request->p_disclaimer;
        }
    
        if($request->__isset('p_detail')){
            $data['p_detail'] = $request->p_detail;
        }
    
        if($request->__isset('p_disease')){
            $data['p_disease'] = $request->p_disease;
        }
    
        if($request->__isset('p_insurance')){
            $data['p_insurance'] = $request->p_insurance;
        }
    
        $data['update_time'] = date('Y-m-d H:i:s');
    
        try{
            $product = new MdlProduct();
            $rs = $product->where("p_id", $id)->update($data);
            return $this->response($rs,0,'修改成功');
        }catch (\Exception $exception){
            return $this->response([],1, $exception->getMessage());
        }
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destroy($id)
    {
        //删除
        try{
            MdlProduct::destroy($id);
            return $this->response([],0,'删除成功');
        }catch(\Exception $exception) {
            return $this->response([],1,$exception->getMessage());
        }
    }
    
    /**
     * 产品佣金费率列表 带条件搜索和分页
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function prodRateList(Request $request){
        $search = [];
    
        if($request->__isset('search_cid'))
            $search['c_id'] = intval($request->search_cid);
    
        if($request->__isset('search_name'))
            $search['p_name'] = trim($request->search_name);
    
        if($request->__isset('search_code'))
            $search['p_code'] = trim($request->search_code);
    
        $perPage = $request->perPage ? $request->perPage : 10;
        $page = $request->page ? $request->page : 1;
    
        $data = MdlProduct::select(['*'])
            ->where(function ($query) use ($search) {
                if (isset($search['c_id']) && !empty($search['c_id'])) {
                    $query->where('c_id', '=', $search['c_id']);
                }
            })
            ->where(function ($query) use ($search) {
                if (isset($search['p_name']) && !empty($search['p_name'])) {
                    $query->where('p_name', 'like', '%' . $search['p_name'] . '%');
                }
            })
            ->where(function ($query) use ($search) {
                if (isset($search['p_code'])) {
                    $query->where('p_code', 'like',  '%' . $search['p_code'] . '%');
                }
            })
            ->orderBy('p_id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        if(!empty($data['data'])){
            $p_ids = array_column($data['data'], 'p_id');
            $
        }
    
        //追加额外参数，例如搜索条件
        $appendData = array(
            'search_cid' => empty($search['c_id']) ? '' : $search['c_id'],
            'search_name' => empty($search['p_name']) ? '' : $search['p_name'],
            'search_property' => !isset($search['p_property']) ? '' : $search['p_property'],
            'search_status' => isset($search['p_status']) ? $search['p_status'] : '',
            'perPage' => $perPage,
        );
    
        $return['data'] = $data;
        $return['paramsData'] = $appendData;
    
        return $this->response($return,0, '保险产品列表');
    }
}
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
use App\Bll\Common\Product\Products;
use App\Model\Product as MdlProduct;
use App\Bll\Common\Product\ProductRates;
use App\Model\ProductRate as MdlProdRate;

class ProductController extends Controller{
    
    /**
     * 带搜索和分页的保险产品列表
     *
     * @param Request $request
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function list(Request $request){
        $search = [];

        if($request->__isset('search_cid') && intval($request->search_cid) > 0)
            $search['c_id'] = intval($request->search_cid);

        if($request->__isset('search_name') && trim($request->search_name) !== '')
            $search['p_name'] = trim($request->search_name);
    
        if($request->__isset('search_code') && trim($request->search_code) !== '')
            $search['p_code'] = trim($request->search_code);

        if($request->__isset('search_property') && intval($request->search_property) !== '')
            $search['p_property'] = intval($request->search_property);

        if($request->__isset('search_status') && intval($request->search_status) !== '')
            $search['p_status'] = intval($request->search_status);

        $perPage = $request->perPage ? $request->perPage : 10;
        $page = $request->page ? $request->page : 1;

        $data = Products::getProductList($search, $perPage, $page);

        //追加额外参数，例如搜索条件
        $appendData = array(
            'search_cid' => empty($search['c_id']) ? '' : $search['c_id'],
            'search_name' => !isset($search['p_name']) ? '' : $search['p_name'],
            'search_property' => !isset($search['p_property']) ? '' : $search['p_property'],
            'search_status' => isset($search['p_status']) ? $search['p_status'] : '',
            'search_code' => !isset($search['p_code']) ? '' : $search['p_code'],
            'perPage' => $perPage,
        );

        $return['listData'] = $data;
        $return['paramsData'] = $appendData;

        return $this->response()->success($return);
    }
    
    /**
     * 获取全部保险产品列表
     *
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function index(){
        $data = MdlProduct::all();
        return $this->response()->success($data);
    }
    
    public function create(){
        //跳到添加view
        return view('product.add');
    }
    
    /**
     * 添加产品
     *
     * @param Request $request
     * @return \App\Http\Controllers\返回一个response的对像|\App\Http\Controllers\返回错误异常
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
            return $this->response()->error('参数错误',-200);
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
            return $this->response()->success('添加成功');
        }catch (\Exception $exception){
            return $this->response()->responseException($exception);
        }
    }
    
    /**
     * 保险产品详情
     *
     * @param $id
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function show($id){
        $p = new MdlProduct();
        $res = $p->findOne(intval($id));
        
        if(empty($res))
            return $this->response()->error('无数据', -200);
        else
            return $this->response()->success($res);
    }
    
    /**
     * 跳转到编辑产品详情页
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
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
     * @return \App\Http\Controllers\返回一个response的对像|\App\Http\Controllers\返回错误异常
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
            return $this->response()->error('参数错误', -200);
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
    
        if($request->__isset('p_short_name')){
            $data['p_short_name'] = $request->p_short_name;
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
            return $this->response()->error('请输入保险期间', -200);
    
        if($data['p_pay_type'] <= 0 || $data['p_pay_time'] <= 0)
            return $this->response()->error('请输入缴费期间', -200);
    
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
            return $this->response()->success('修改成功');
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
            MdlProduct::destroy($id);
            return $this->response()->success('删除成功');
        }catch(\Exception $exception) {
            return $this->response()->responseException($exception);
        }
    }
    
    /**
     * 根据产品代码获取产品信息
     *
     * @param Request $request
     * @return \App\Http\Controllers\引发一个http请求的错误异常|\App\Http\Controllers\返回一个response的对像
     */
    public function getInfoByCode(Request $request){
        if(empty($request->p_code))
            return $this->response()->error('无数据', -200);
    
        $p_code = trim($request->p_code);
        $mdlPro = new MdlProduct();
        $info = $mdlPro->where('p_code', '=', $p_code)->get(['p_id', 'p_name', 'p_short_name', 'p_code', 'p_property', 'p_status']);

        if(empty($info[0]))
            return $this->response()->error('无数据', -200);
        else
            return $this->response()->success($info[0]);
    }
    
    /**
     * 产品佣金费率列表 带条件搜索和分页
     *
     * @param Request $request
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function prodRateList(Request $request){
        $search = [];
    
        if($request->__isset('search_cid') && $request->search_cid !== '')
            $search['c_id'] = intval($request->search_cid);
    
        if($request->__isset('search_name') && trim($request->search_name) !== '')
            $search['p_name'] = trim($request->search_name);
    
        if($request->__isset('search_code') && trim($request->search_code) !== '')
            $search['p_code'] = trim($request->search_code);
    
        $perPage = $request->perPage ? $request->perPage : 10;
        $page = $request->page ? $request->page : 1;
        
        $data = Products::getProductList($search, $perPage, $page);
    
        if(!empty($data->items())){
            $p_ids = array_column($data->items(), 'p_id');
            $rateBll = new ProductRates();
            $rate = $rateBll->getRatesByPIds($p_ids);
    
            foreach ($data->items() as $key => $value) {
                $data->items()[$key]['r_id'] = isset($rate[$value['p_id']]) ? $rate[$value['p_id']] : 0;
            }
        }
    
        //追加额外参数，例如搜索条件
        $appendData = array(
            'search_cid' => empty($search['c_id']) ? '' : $search['c_id'],
            'search_name' => !isset($search['p_name']) ? '' : $search['p_name'],
            'search_code' => !isset($search['p_code']) ? '' : $search['p_code'],
            'perPage' => $perPage,
            'page' => $page
        );

        $return['listData'] = $data;
        $return['paramsData'] = $appendData;
    
        return $this->response()->success($return);
    }
    
    /**
     * 产品佣金费率详情
     *
     * @param $id
     * @return \App\Http\Controllers\返回一个response的对像|\App\Http\Controllers\返回错误异常
     */
    public function prodRateInfo($id){
        $pr = new MdlProdRate();
        $res = $pr->findOne(intval($id));
        
        if(empty($res))
            return $this->response()->error('无数据', -200);
        
        if(!empty($res->r_data)){
            $res->r_data = json_decode($res->r_data, true);
        }
            
        return $this->response()->success($res);
    }
    
    /**
     * 编辑保险产品佣金费率
     *
     * @param $id
     * @return \App\Http\Controllers\引发一个http请求的错误异常|\App\Http\Controllers\返回一个response的对像
     */
    public function prodRateEdit($id){
        if(intval($id) <= 0)
            return $this->response()->error('参数错误', -200);
        
        $objProduct = new MdlProduct();
        $data = $objProduct->where('p_id', '=', $id)
            ->first(['p_id', 'c_id', 'p_code', 'p_name', 'p_short_name']);
        
        if(empty($data))
            return $this->response()->error('无数据', -200);

        $mdlRate = new MdlProdRate();
        $rate = $mdlRate->where('p_id', '=', $id)->first(['r_id', 'r_data', 'add_time', 'update_time']);
        $data['r_id'] = empty($rate['r_id']) ? 0 : $rate['r_id'];
        $data['r_data'] = [];
        $data['add_time'] = empty($rate['add_time']) ? '' : $rate['add_time'];
        $data['update_time'] = empty($rate['update_time']) ? '' : $rate['update_time'];

        if (!empty($rate['r_data'])) {
            $r_data = json_decode($rate['r_data'], true);
            $data['r_data'] = $r_data;
        }
        
        return $this->response()->success($data);
    }
    
    /**
     * 保存保险产品佣金费率
     *
     * @param Request $request
     * @return \App\Http\Controllers\引发一个http请求的错误异常|\App\Http\Controllers\返回一个response的对像|\App\Http\Controllers\返回错误异常
     */
    public function prodRateSave(Request $request){
        if(!$request->__isset('p_id') || (int)$request->p_id <= 0)
            return $this->response()->error('参数错误', -200);
        else
            $p_id = (int)$request->p_id;
    
        $objProduct = new MdlProduct();
        $p_row = $objProduct->where('p_id', '=', $p_id)->first();
        if(empty($p_row)){
            return $this->response()->error('无数据', -200);
        }
    
        $objProdRate = new MdlProdRate();
        $r_row = $objProdRate->where('p_id', '=', $p_id)->first();
        
        if($r_row){
            $data['r_data'] = empty($request->r_data) ? '' : $request->r_data;
            $data['update_time'] = date('Y-m-d H:i:s');
            
            try {
                $objProdRate->where('p_id', '=', $p_id)->update($data);
                return $this->response()->success('编辑成功');
            }catch (\Exception $exception){
                return $this->response()->responseException($exception);
            }
        }else{
            $objProdRate->p_id = (int)$request->p_id;
            $objProdRate->r_data = empty($request->r_data) ? '' : $request->r_data;
            $objProdRate->add_time = date('Y-m-d H:i:s');
            $objProdRate->update_time = date('Y-m-d H:i:s');
            
            try {
                $objProdRate->save();
                return $this->response()->success('添加成功');
            }catch (\Exception $exception){
                return $this->response()->responseException($exception);
            }
        }
    }
}
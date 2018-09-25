<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/21
 * Time: 10:41
 */

namespace App\Http\Controllers\API;

use App\Model\Policy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Flysystem\Exception;

class PolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\Policy  $policy
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $policy = new Policy();
        $data = $policy->findOne($id);
        
        if(empty($data))
            return $this->response()->error('暂无数据', -200);
        
        return $this->response()->success($data);
    }
    
    /**
     * 跳转到投保单、保单编辑页
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $policy = new Policy();
        $data = $policy->findOne($id);
        
        return view('policy.add', ['data' => $data]);
    }
    
    /**
     * 投保单、保单编辑
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $policy = new Policy();
        $row = $policy->findOne($id);
        
        if(empty($row))
            return $this->response()->error('数据不存在', -200);
    
        try{
            $this->validate($request, [
                'c_id' => 'required|integer',
                'tb_code' => 'required|unique:c_insurance_slip,tb_code,' . $id . ',s_id',
                'ag_id' => 'required|integer',
                'o_id' => 'required|integer',
                'tb_date' => 'required|date',
                'tb_name' => 'required',
                'tb_id_type' => 'required|integer',
                'tb_id_number' => 'required',
                'tb_sex' => 'required|integer',
                'tb_birthday' => 'required|date',
                'tb_id_longtime' => 'required|integer',
                'tb_phone' => 'required|numeric|max:11',
                'tb_addr' => 'required',
                'bb_tb_relation' => 'required|integer',
                'f_type' => 'required|integer',
                'pay_first' => 'required|numeric',
                'pay_name' => 'required',
                'pay_account' => 'required',
                'pay_bank' => 'required',
            ]);
        }catch (\Exception $exception){
            return $this->response()->error('请正确填写必填项', -200);
        }
        
        $data['c_id'] = intval($request->c_id);
        $data['tb_code'] = trim($request->tb_code);
        $data['ag_id'] = intval($request->ag_id);
        $data['o_id'] = intval($request->o_id);
        $data['tb_date'] = $request->tb_date;
        $data['tb_name'] = trim($request->tb_name);
        $data['tb_id_type'] = intval($request->tb_id_type);
        $data['pay_first'] = trim($request->pay_first);
        $data['pay_name'] = intval($request->pay_name);
        $data['pay_account'] = trim($request->pay_account);
        $data['pay_bank'] = intval($request->pay_bank);
        
        if($data['tb_id_type'] == 0){
            try{
                $this->validate($request, ['tb_id_number' => 'required|min:16|max:18']);
            }catch (\Exception $exception){
                return $this->response()->error('请正确填写投保人证件号码', -200);
            }
        }
        
        $data['tb_id_number'] = trim($request->tb_id_number);
        $data['tb_sex'] = intval($request->tb_sex);
        $data['tb_birthday'] = $request->tb_birthday;
        $data['tb_id_longtime'] = intval($request->tb_id_longtime);
        
        if (!$data['tb_id_longtime']) {
            try{
                $this->validate($request, ['tb_id_date' => 'required|date']);
            }catch (\Exception $exception){
                return $this->response()->error('请正确填写投保人证件有效期', -200);
            }
            
            $data['tb_id_date'] = $request->tb_id_date;
        }
        
        if($request->__isset('tb_company')){
            $data['tb_company'] = empty($request->tb_company) ? '' : $request->tb_company;
        }
    
        if($request->__isset('tb_job')){
            $data['tb_job'] = empty($request->tb_job) ? '' : $request->tb_job;
        }
        
        if(!preg_match('/^1[3|4|5|7|8]\d{9}$/', $request->tb_phone)){
            return $this->response()->error('请正确填写投保人手机号码', -200);
        }
        $data['tb_phone'] = trim($request->tb_phone);

        if($request->__isset('tb_postcode')){
            try{
                $this->validate($request, [
                    'tb_postcode' => 'integer|max:6'
                ]);
            }catch (\Exception $exception){
                return $this->response()->error('请正确填写投保人邮编', -200);
            }
    
            $data['tb_postcode'] = empty($request->tb_postcode) ? '' : $request->tb_postcode;
        }
    
        if($request->__isset('tb_email')){
            try{
                $this->validate($request, [
                    'tb_email' => 'email'
                ]);
            }catch (\Exception $exception){
                return $this->response()->error('请正确填写投保人邮箱地址', -200);
            }
        
            $data['tb_email'] = empty($request->tb_email) ? '' : $request->tb_email;
        }
    
        $data['tb_addr'] = $request->tb_addr;
        $data['bb_tb_relation'] = intval($request->bb_tb_relation);
        $data['f_type'] = intval($request->f_type);
        
        //被保人与投保人关系：非本人，记录被保人信息
        if(!$data['bb_tb_relation']){
            try{
                $this->validate($request, [
                    'ir_name' => 'required',
                    'ir_id_type' => 'required|integer',
                    'ir_id_number' => 'required',
                    'ir_sex' => 'required|integer',
                    'ir_birthday' => 'required|date',
                    'ir_id_longtime' => 'required|integer',
                    'ir_phone' => 'required|numeric|max:11',
                    'ir_addr' => 'required',
                ]);
            }catch (\Exception $exception){
                return $this->response()->error('请正确填写必填项', -200);
            }
    
            $ir_data['s_id'] = $id;
            $ir_data['ir_name'] = trim($request->ir_name);
            $ir_data['ir_id_type'] = intval($request->ir_id_type);
    
            if($ir_data['ir_id_type'] == 0){
                try{
                    $this->validate($request, ['ir_id_number' => 'required|min:16|max:18']);
                }catch (\Exception $exception){
                    return $this->response()->error('请正确填写被保人证件号码', -200);
                }
            }
    
            $ir_data['ir_id_number'] = trim($request->ir_id_number);
            $ir_data['ir_sex'] = intval($request->ir_sex);
            $ir_data['ir_birthday'] = $request->ir_birthday;
            $ir_data['ir_id_longtime'] = intval($request->ir_id_longtime);
    
            if (!$ir_data['ir_id_longtime']) {
                try{
                    $this->validate($request, ['ir_id_date' => 'required|date']);
                }catch (\Exception $exception){
                    return $this->response()->error('请正确填写被保人证件有效期', -200);
                }
    
                $ir_data['ir_id_date'] = $request->ir_id_date;
            }
    
            if($request->__isset('ir_company')){
                $ir_data['ir_company'] = empty($request->ir_company) ? '' : $request->ir_company;
            }
    
            if($request->__isset('ir_job')){
                $ir_data['ir_job'] = empty($request->ir_job) ? '' : $request->ir_job;
            }
    
            if(!preg_match('/^1[3|4|5|7|8]\d{9}$/', $request->ir_phone)){
                return $this->response()->error('请正确填写被保人手机号码', -200);
            }
            $ir_data['ir_phone'] = trim($request->ir_phone);
    
            if($request->__isset('ir_postcode')){
                try{
                    $this->validate($request, [
                        'ir_postcode' => 'integer|max:6'
                    ]);
                }catch (\Exception $exception){
                    return $this->response()->error('请正确填写被保人邮编', -200);
                }
    
                $ir_data['ir_postcode'] = empty($request->ir_postcode) ? '' : $request->ir_postcode;
            }
    
            if($request->__isset('ir_email')){
                try{
                    $this->validate($request, [
                        'ir_email' => 'email'
                    ]);
                }catch (\Exception $exception){
                    return $this->response()->error('请正确填写被保人邮箱地址', -200);
                }
    
                $ir_data['ir_email'] = empty($request->ir_email) ? '' : $request->ir_email;
            }
    
            $ir_data['ir_addr'] = $request->ir_addr;
        }
        
        //受益人为指定受益人：记录指定受益人信息
        if(!$data['f_type']){
            if(!$request->__isset('favoree') || empty($request->favoree))
                return $this->response()->error('请至少填写一位受益人信息', -200);
            
            if(is_string($request->favoree))
                $request->favoree = json_decode($request->favoree, true);
            
            if(is_array($request->favoree)){
                foreach ($request->favoree as $key => $item) {
                    try{
                        $this->validate($item, [
                            'f_sort' => 'required|integer',
                            'f_percent' => 'required|numeric',
                            'f_name' => 'required',
                            'f_id_type' => 'required|integer',
                            'f_id_number' => 'required|min:16|max:18',
                            'f_sex' => 'required|integer',
                            'f_birthday' => 'required|date',
                            'f_phone' => 'required|numeric|max:11'
                        ]);
                    }catch (\Exception $exception){
                        return $this->response()->error('请正确填写受益人信息', -200);
                    }
                    
                    if(isset($item['f_id']) && intval($item['f_id']) > 0){
                        $f_edit_data[$key] = $item;
                    }else{
                        $f_add_data[$key] = $item;
                        $f_add_data[$key]['s_id'] = $id;
                    }
                }
            }
        }
        
        //保险产品数据
        if(!$request->__isset('products') || empty($request->products))
            return $this->response()->error('请添加险种资料', -200);
        
        if(is_string($request->products)){
            $request->products = json_decode($request->products, true);
        }
        
        if(is_array($request->products)){
            foreach ($request->products as $pk => $product) {
                try{
                    $this->validate($product, [
                        'p_id' => 'required|integer',
                        'sp_property' => 'required|integer',
                        'sp_bao_type' => 'required|integer',
                        'sp_pay_type' => 'required|integer',
                        'sp_pay_time' => 'required|integer',
                        'sp_pay_way' => 'required|integer',
                        'sp_money' => 'required|numeric',
                        'sp_premium' => 'required|numeric'
                    ]);
                }catch (\Exception $exception){
                    return $this->response()->error('请正确填写险种资料', -200);
                }
                
                if($product['sp_bao_type'] && empty($product['sp_bao_time'])){
                    return $this->response()->error('请正确填写险种保险期间', -200);
                }
    
                if(isset($product['sp_id']) && intval($product['sp_id']) > 0){
                    $sp_edit_data[$pk] = $product;
                }else{
                    $sp_add_data[$pk] = $product;
                    $sp_add_data[$pk]['s_id'] = $id;
                }
            }
        }
        
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Policy  $policy
     * @return \Illuminate\Http\Response
     */
    public function destroy(Policy $policy)
    {
        //
    }
}

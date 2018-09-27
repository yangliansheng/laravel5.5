<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/21
 * Time: 10:41
 */

namespace App\Http\Controllers\API;

use App\Bll\Common\Policy\Bank;
use App\Model\Policy;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\PolicyRecognizee;
use App\Model\PolicyFavoree;
use App\Model\PolicyProduct;
use App\Bll\Common\Policy\Policy as BllPolicy;
use App\Model\Company;
use App\Model\Agent;
use App\Model\Organization;
use App\Model\Product;


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
     * 投保单录入
     *
     * @param Request $request
     * @return \App\Http\Controllers\返回一个response的对像|\App\Http\Controllers\返回错误异常
     */
    public function store(Request $request)
    {
        try{
            $this->validate($request, [
                'c_id' => 'required|integer',
                'tb_code' => 'required|unique:c_insurance_slip,tb_code',
                'ag_id' => 'required|integer',
                'o_id' => 'required|integer',
                'tb_date' => 'required|date',
                'tb_name' => 'required|max:50',
                'tb_id_type' => 'required|integer',
                'tb_id_number' => 'required',
                'tb_id_longtime' => 'required|integer',
                'tb_sex' => 'required|integer',
                'tb_birthday' => 'required|date',
                'tb_phone' => 'required|max:11',
                'tb_addr' => 'required',
                'bb_tb_relation' => 'required|integer',
                'f_type' => 'required|integer',
                'pay_first' => 'required|numeric',
                'pay_name' => 'required|max:50',
                'pay_account' => 'required',
                'pay_bank' => 'required',
            ]);
        }catch (\Exception $exception){
            return $this->response()->error('参数错误',-200);
        }

        $policy = new Policy();
        $policy->c_id = intval($request->c_id);
        $policy->ag_id = intval($request->ag_id);
        $policy->o_id = intval($request->o_id);
        $policy->tb_code = trim($request->tb_code);
        $policy->tb_date = trim($request->tb_date);
        $policy->tb_name = trim($request->tb_name);
        $policy->tb_id_type = intval($request->tb_id_type);

        if(!$policy->tb_id_type){
            if(!BllPolicy::isCreditNo($request->tb_id_number)){
                return $this->response()->error('请正确填写投保人证件号码', -200);
            }
        }

        $policy->tb_id_number = trim($request->tb_id_number);
        $policy->tb_sex = intval($request->tb_sex);
        $policy->tb_birthday = trim($request->tb_birthday);
        $policy->tb_id_longtime = intval($request->tb_id_longtime);

        if(!$policy->tb_id_longtime && empty(trim($request->tb_id_date)))
            return $this->response()->error('请正确填写投保人证件有效期', -200);
        else
            $policy->tb_id_date = trim($request->tb_id_date);

        if($request->__isset('tb_company') && trim($request->tb_company) != '')
            $policy->tb_company = trim($request->tb_company);

        if($request->__isset('tb_job') && trim($request->tb_job) != '')
            $policy->tb_job = trim($request->tb_job);

        if(!preg_match('/^1[3|4|5|7|8]\d{9}$/', $request->tb_phone))
            return $this->response()->error('请正确填写投保人手机号码', -200);
        else
            $policy->tb_phone = trim($request->tb_phone);

        if($request->__isset('tb_postcode') && trim($request->tb_postcode) != ''){
            if(!preg_match('/^[0-9]\d{5}$/', trim($request->tb_postcode)))
                return $this->response()->error('请正确填写投保人邮编', -200);

            $policy->tb_postcode = trim($request->tb_postcode);
        }

        if($request->__isset('tb_email') && trim($request->tb_email) != ''){
            if(!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', trim($request->tb_email)))
                return $this->response()->error('请正确填写投保人Email', -200);

            $policy->tb_email = trim($request->tb_email);
        }

        $policy->tb_addr = trim($request->tb_addr);
        $policy->bb_tb_relation = intval($request->bb_tb_relation);
        $policy->f_type = intval($request->f_type);

        //被保人非投保人，记录被保人信息
        if(!$policy->bb_tb_relation){
            try{
                $this->validate($request, [
                    'ir_name' => 'required|max:50',
                    'ir_id_type' => 'required|integer',
                    'ir_id_number' => 'required',
                    'ir_id_longtime' => 'required|integer',
                    'ir_sex' => 'required|integer',
                    'ir_birthday' => 'required|date',
                    'ir_phone' => 'required|max:11',
                    'ir_addr' => 'required',
                ]);
            }catch (\Exception $exception){
                return $this->response()->error('请正确填写被保人资料',-200);
            }

            $recognizee['ir_name'] = trim($request->ir_name);
            $recognizee['ir_id_type'] = intval($request->ir_id_type);

            //证件类型为身份证，则验证身份证号
            if(!$recognizee['ir_id_type']){
                if(!BllPolicy::isCreditNo(trim($request->ir_id_number)))
                    return $this->response()->error('请正确填写被保人证件号码',-200);
            }

            $recognizee['ir_id_number'] = trim($request->ir_id_number);
            $recognizee['ir_sex'] = intval($request->ir_sex);
            $recognizee['ir_birthday'] = trim($request->ir_birthday);
            $recognizee['ir_id_longtime'] = intval($request->ir_id_longtime);

            if(!$recognizee['ir_id_longtime']){
                if (empty(trim($request->ir_id_date)))
                    return $this->response()->error('请正确填写被保人证件有效期', -200);
                else
                    $recognizee['ir_id_date'] = trim($request->ir_id_date);
            }

            if($request->__isset('ir_company') && trim($request->ir_company) != '')
                $recognizee['ir_company'] = trim($request->ir_company);

            if($request->__isset('ir_job') && trim($request->ir_job) != '')
                $recognizee['ir_job'] = trim($request->ir_job);

            if(!preg_match('/^1[3|4|5|7|8]\d{9}$/', $request->ir_phone))
                return $this->response()->error('请正确填写被保人手机号码', -200);
            else
                $recognizee['ir_phone'] = trim($request->ir_phone);

            if($request->__isset('ir_postcode') && trim($request->ir_postcode) != ''){
                if(!preg_match('/^[0-9]\d{5}$/', trim($request->ir_postcode)))
                    return $this->response()->error('请正确填写被保人邮编', -200);

                $recognizee['ir_postcode'] = trim($request->ir_postcode);
            }

            if($request->__isset('ir_email') && trim($request->ir_email) != ''){
                if(!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', trim($request->ir_email)))
                    return $this->response()->error('请正确填写被保人Email', -200);

                $recognizee['ir_email'] = trim($request->ir_email);
            }

            $recognizee['ir_addr'] = trim($request->ir_addr);
            $recognizee['add_time'] = date('Y-m-d H:i:s');
            $recognizee['update_time'] = date('Y-m-d H:i:s');
        }

        //受益人为指定受益人，记录受益人信息
        $favoree = [];
        if($policy->f_type){
            $request->favoree = [
                ['f_percent' => '50', 'f_name' => '小红', 'f_id_type' => 0, 'f_id_number' => '1234567890123456', 'f_birthday' => '1998-10-10', 'f_phone' => '18998765432', 'f_sex' => 1],
                ['f_percent' => '30', 'f_name' => '小明', 'f_id_type' => 1, 'f_id_number' => 'jiashizheng002', 'f_birthday' => '2000-1-1', 'f_phone' => '17712345678', 'f_sex' => 0]
            ];

            if(empty($request->favoree))
                return $this->response()->error('请正确填写受益人资料', -200);

            foreach ($request->favoree as $key => $item) {
                $validator = Validator::make($item, [
                    'f_percent' => 'required|numeric',
                    'f_name' => 'required|max:50',
                    'f_id_type' => 'required|integer',
                    'f_id_number' => 'required',
                    'f_birthday' => 'required|date',
                    'f_phone' => 'required|max:11',
                    'f_sex' => 'required|integer',
                ]);

                if ($validator->fails()) {
                    return $this->response()->error('受益人资料填写有误', -200);
                }

                $favoree[$key]['f_sort'] = empty($item['f_sort']) ? 0 : intval($item['f_sort']);
                $favoree[$key]['f_percent'] = $item['f_percent'];
                $favoree[$key]['f_name'] = trim($item['f_name']);
                $favoree[$key]['f_id_type'] = intval($item['f_id_type']);

                if(!$favoree[$key]['f_id_type'] && !BllPolicy::isCreditNo(trim($item['f_id_number'])))
                    return $this->response()->error('请正确填写受益人证件号码', -200);

                $favoree[$key]['f_id_number'] = trim($item['f_id_number']);
                $favoree[$key]['f_sex'] = intval($item['f_sex']);
                $favoree[$key]['f_birthday'] = trim($item['f_birthday']);

                if(!preg_match('/^1[3|4|5|7|8]\d{9}$/', trim($item['f_phone'])))
                    return $this->response()->error('请正确填写受益人手机号码', -200);

                $favoree[$key]['f_phone'] = trim($item['f_phone']);
                $favoree[$key]['add_time'] = date('Y-m-d H:i:s');
                $favoree[$key]['update_time'] = date('Y-m-d H:i:s');
            }
        }

        //险种资料
        $products = [];
        $request->products = [
            ['p_id' => '1', 'sp_property' => 1, 'sp_bao_type' => 0, 'sp_bao_time' => '', 'sp_pay_type' => 1, 'sp_pay_time' => 50, 'sp_pay_way' => 1, 'sp_money' => 1000000, 'sp_premium' => 15678.94],
            ['p_id' => 1, 'sp_property' => 2, 'sp_bao_type' => 2, 'sp_bao_time' => 1, 'sp_pay_type' => 2, 'sp_pay_time' => 1, 'sp_pay_way' => 0, 'sp_money' => 3000, 'sp_premium' => 125.68]
        ];

        if(empty($request->products))
            return $this->response()->error('请正确填写险种资料', -200);

        if(is_string($request->products))
            $request->products = json_decode($request->products, true);

        if(!is_array($request->products))
            return $this->response()->error('险种资料数据格式有误', -200);

        foreach ($request->products as $pk => $product) {
            $validator = Validator::make($product, [
                'p_id' => 'required|integer',
                'sp_property' => 'required|integer',
                'sp_bao_type' => 'required|integer',
                'sp_pay_type' => 'required|integer',
                'sp_pay_time' => 'required|integer',
                'sp_pay_way' => 'required|integer',
                'sp_money' => 'required|numeric',
                'sp_premium' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return $this->response()->error('请正确填写险种资料', -200);
            }

            $products[$pk]['p_id'] = intval($product['p_id']);
            $products[$pk]['sp_property'] = intval($product['sp_property']);

            if($products[$pk]['sp_property'] == 1)
                $products[$pk]['sp_auto'] = 1;//主险默认自动续保
            else
                $products[$pk]['sp_auto'] = empty($product['sp_auto']) ? 0 : intval($product['sp_auto']);

            $products[$pk]['sp_bao_type'] = intval($product['sp_bao_type']);
            if($products[$pk]['sp_bao_type']){
                if(empty($product['sp_bao_time']))
                    return $this->response()->error('请正确填写险种保险期间', -200);
            }

            $products[$pk]['sp_bao_time'] = empty($product['sp_bao_time']) ? 0 : intval($product['sp_bao_time']);
            $products[$pk]['sp_pay_type'] = intval($product['sp_pay_type']);
            $products[$pk]['sp_pay_time'] = intval($product['sp_pay_time']);
            $products[$pk]['sp_pay_way'] = intval($product['sp_pay_way']);
            $products[$pk]['sp_money'] = round($product['sp_money'], 2);
            $products[$pk]['sp_premium'] = round($product['sp_premium'], 2);
            $products[$pk]['add_time'] = date('Y-m-d H:i:s');
            $products[$pk]['update_time'] = date('Y-m-d H:i:s');
        }

        $policy->pay_first = round($request->pay_first, 2);
        $policy->pay_name = trim($request->pay_name);
        $policy->pay_account = trim($request->pay_account);
        $policy->pay_bank = trim($request->pay_bank);
        $policy->tb_status = intval($request->tb_status);
        $policy->add_time = date('Y-m-d H:i:s');
        $policy->update_time = date('Y-m-d H:i:s');

        try{
            $rs = $policy->save();
            $s_id = $policy->s_id;

            //投保单、保单数据保存成功之后，保存被保人资料、受益人资料、险种资料

            //保存被保人资料
            if(!empty($recognizee)){
                $recognizee['s_id'] = $s_id;

                $mdlRec = new PolicyRecognizee();
                $rs = $mdlRec->insert($recognizee);
            }

            //保存受益人资料
            if(!empty($favoree)){
                foreach ($favoree as $fk => $fav) {
                    $favoree[$fk]['s_id'] = $s_id;
                }

                $mdlFav = new PolicyFavoree();
                $rs = $mdlFav->insert($favoree);
            }

            //保存险种资料
            if(!empty($products)){
                foreach ($products as $p => $pro) {
                    $products[$p]['s_id'] = $s_id;
                }

                $mdlPro = new PolicyProduct();
                $rs = $mdlPro->insert($products);
            }

            return $this->response()->success('添加成功');
        }catch (\Exception $exception){
            return $this->response()->responseException($exception);
        }
    }

    /**
     * 投保单详情
     *
     * @param $id
     * @return \App\Http\Controllers\引发一个http请求的错误异常|\App\Http\Controllers\返回一个response的对像
     */
    public function show($id)
    {
        $policy = new Policy();
        $data = $policy->findOne($id);

        if(empty($data))
            return $this->response()->error('无数据', -200);

        //获取保险公司名称
        $mdlCom = new Company();
        $company = $mdlCom->findOne($data->c_id);
        $data->c_name = empty($company) ? '' : $company->c_name;

        //获取代理人信息
        $mdlAgent = new Agent();
        $agent = $mdlAgent->where('ag_id', '=', $data->ag_id)->get(['ag_name', 'ag_code', 'ag_job_number']);
        $data->ag_name = empty($agent[0]['ag_name']) ? '' : $agent[0]['ag_name'];
        $data->ag_code = empty($agent[0]['ag_code']) ? '' : $agent[0]['ag_code'];
        $data->ag_job_number = empty($agent[0]['ag_job_number']) ? '' : $agent[0]['ag_job_number'];

        //获取机构信息
        $mdlOrg = new Organization();
        $organization = $mdlOrg->where('o_id', '=', $data->o_id)->get(['o_name', 'o_code']);
        $data->o_name = empty($organization[0]['o_name']) ? '' : $organization[0]['o_name'];
        $data->o_code = empty($organization[0]['o_code']) ? '' : $organization[0]['o_code'];
        
        //投保人与被保人不是同一人，那么获取被保人信息
        if(!$data->bb_tb_relation){
            $mdlRec = new PolicyRecognizee();
            $data->recognizee = $mdlRec->findOne($data->s_id);
        }
        
        //受益人为指定受益人，那么获取受益人信息
        if($data->f_type){
            $mdlFav = new PolicyFavoree();
            $data->favoree = $mdlFav->where('s_id', '=', $data->s_id)->get();
        }
        
        //获取险种资料
        $mdlPro = new PolicyProduct();
        $data->products = $mdlPro->where('s_id', '=', $data->s_id)->get()->toArray();
        
        if(!empty($data->products)){
            $p_ids = array_column($data->products, 'p_id');
            $p_ids = array_unique($p_ids);
            
            $mdlP = new Product();
            $info = $mdlP->whereIn('p_id', $p_ids)->get(['p_id','p_code','p_name','p_short_name'])->toArray();
            
            if(!empty($info)){
                foreach ($info as $key => $value) {
                    $pInfo[$value['p_id']] = $value;
                }
            }
        }
        
        foreach ($data->products as $item => $product) {
            $data->products[$item]['p_name'] = isset($pInfo[$product['p_id']]['p_name']) ? $pInfo[$product['p_id']]['p_name'] : '';
            $data->products[$item]['p_code'] = isset($pInfo[$product['p_id']]['p_code']) ? $pInfo[$product['p_id']]['p_code'] : '';
        }
        
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
//                try{

//                    $this->validate($product, [
//                        'p_id' => 'required|integer',
//                        'sp_property' => 'required|integer',
//                        'sp_bao_type' => 'required|integer',
//                        'sp_pay_type' => 'required|integer',
//                        'sp_pay_time' => 'required|integer',
//                        'sp_pay_way' => 'required|integer',
//                        'sp_money' => 'required|numeric',
//                        'sp_premium' => 'required|numeric'
//                    ]);
//                }catch (\Exception $exception){
//                    return $this->response()->error('请正确填写险种资料', -200);
//                }
    
                $validator = Validator::make($product, [
                    'p_id' => 'required|integer',
                    'sp_property' => 'required|integer',
                    'sp_bao_type' => 'required|integer',
                    'sp_pay_type' => 'required|integer',
                    'sp_pay_time' => 'required|integer',
                    'sp_pay_way' => 'required|integer',
                    'sp_money' => 'required|numeric',
                    'sp_premium' => 'required|numeric'
                ]);
    
                if ($validator->fails()) {
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
    
    /**
     * 验证身份证号有效性，并根据身份证号获取出生日期和性别
     *
     * @param Request $request
     * @return \App\Http\Controllers\引发一个http请求的错误异常|\App\Http\Controllers\返回一个response的对像
     */
    public function getIDCardInfo(Request $request){
        if(!empty($request->idcard)){
            $info = BllPolicy::getIDCardInfo(trim($request->idcard));
            
            if($info['status'])
                return $this->response()->success($info);
        }
    
        return $this->response()->error('请输入有效的证件号码', -200);
    }
    
    /**
     * 根据银行卡号获取所属银行
     *
     * @param Request $request
     * @return \App\Http\Controllers\返回一个response的对像
     */
    public function getBankByCard(Request $request){
        if(!empty($request->card)){
            $bank = Bank::getBankByCard($request->card);
    
            return $this->response()->success($bank);
        }else{
            return $this->response()->error('请输入正确的银行卡号', -200);
        }
    }
}

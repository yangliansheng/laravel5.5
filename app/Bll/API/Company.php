<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/19
 * Time: 14:43
 */

namespace App\Bll\API;

use App\Model\Company as MdlCompany;

class Company{
    private $objCompany;
    
    /**
     * 注入一个机构等级数据库实例
     * OrganizationGrade constructor.
     */
    public function __construct() {
        $this->objCompany = new MdlCompany();
    }
    
    /**
     * @param $property
     * 实例操作
     * @return mixed
     */
    public function __get($property) {
        return $this->objCompany->$property;
    }
    
    /**
     * 实例操作
     * @param $property
     * @param $value
     */
    public function __set($property, $value) {
        $this->objCompany->$property = $value;
    }
    
    /**
     * 获取保险公司列表 按搜索条件和分页
     *
     * @param $search
     * @param $perPage
     * @param $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($search = [], $perPage = 10, $page = 1){
        $m_company = new MdlCompany();
        
        $data = $m_company->select(['*'])
            ->where(function ($query) use ($search) {
                if (isset($search['c_name']) && !empty($search['c_name'])) {
                    $query->where('c_name', 'like', '%' . $search['c_name'] . '%');
                }
            })
            ->where(function ($query) use ($search) {
                if (isset($search['c_short_name']) && !empty($search['c_short_name'])) {
                    $query->where('c_short_name', 'like', '%' . $search['c_short_name'] . '%');
                }
            })
            ->where(function ($query) use ($search) {
                if (isset($search['c_code']) && !empty($search['c_code'])) {
                    $query->where('c_code', 'like', '%' . $search['c_code'] . '%');
                }
            })
            ->where(function ($query) use ($search) {
                if (isset($search['c_status'])) {
                    $query->where('c_status', '=', $search['c_status']);
                }
            })
            ->orderBy('c_id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        return $data;
    }
    
    /**
     * 验证保险公司代码是否已存在
     *
     * @param string $c_code
     * @param int $c_id
     * @return bool
     */
    public function codeIsExist($c_code='', $c_id=0){
        if(empty($c_code))
            return false;
        else{
            $c_id = intval($c_id);
            
            $rs = $this->objCompany
                ->where('c_code', '=', $c_code)
                ->where(function ($query) use ($c_id){
                    if($c_id > 0){
                        $query->where('c_id', '<>', $c_id);
                    }
                })->count();
            
            if($rs)
                return true;
            else
                return false;
        }
    }
}
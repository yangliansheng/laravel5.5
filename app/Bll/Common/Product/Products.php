<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/25
 * Time: 15:19
 */

namespace App\Bll\Common\Product;

use App\Model\Product;

class Products{
    protected $mdlProduct;
    
    public function __construct()
    {
        $this->mdlProduct = new Product();
    }
    
    /**
     * 根据搜索条件获取产品分页列表
     *
     * @param array $search
     * @param int $perPage
     * @param int $page
     * @return mixed
     */
    public static function getProductList($search = [], $perPage = 10, $page = 1){
        $m_product = new Product();
    
        $data = $m_product->select(['*'])
            ->where(function ($query) use ($search) {
                if (isset($search['c_id']) && !empty($search['c_id'])) {
                    $query->where('c_id', '=', $search['c_id']);
                }
            })
            ->where(function ($query) use ($search) {
                if (isset($search['p_name'])) {
                    $query->where('p_name', 'like', '%' . $search['p_name'] . '%');
                }
            })
            ->where(function ($query) use ($search) {
                if (isset($search['p_code'])) {
                    $query->where('p_code', 'like', '%' . $search['p_code'] . '%');
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
    
        return $data;
    }
}
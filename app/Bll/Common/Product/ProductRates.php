<?php
/**
 * Created by PhpStorm.
 * User: zlj
 * Date: 2018/9/25
 * Time: 17:26
 */

namespace App\Bll\Common\Product;

use App\Model\ProductRate;

class ProductRates{
    protected $mdlRate;
    
    public function __construct()
    {
        $this->mdlRate = new ProductRate();
    }
    
    public function getRatesByPIds($p_ids = ''){
        $rate = $this->mdlRate->whereIn('p_id', $p_ids)->get();
    
        $d = [];
        if(!empty($rate)){
            foreach ($rate as $item) {
                $d[$item->p_id] = $item->r_id;
            }
        }
        
        return $d;
    }
}
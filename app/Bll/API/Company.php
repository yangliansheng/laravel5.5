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
    
    /**
     * 获取保险公司列表 按搜索条件和分页
     *
     * @param $search
     * @param $perPage
     * @param $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($search = [], $perPage = 10, $page = 1){
        $data = MdlCompany::select(['c_id', 'c_name', 'c_short_name', 'c_code', 'c_tel', 'c_status', 'c_start', 'c_end', 'add_time', 'update_time'])
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
}
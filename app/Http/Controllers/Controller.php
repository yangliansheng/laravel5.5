<?php

namespace App\Http\Controllers;

use App\Bll\Common\Account\AccountManage;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    /**
     * 是否超管
     * @var bool
     */
    protected $isAdminer = false;
    
    /**
     * 获取超管
     */
    protected function getIsAdminer() {
        $this->bindingUser();
        $account = new AccountManage($this->LoginUser);
        if($account->isAdminer()) {
            $this->isAdminer = true;
        }
    }
}

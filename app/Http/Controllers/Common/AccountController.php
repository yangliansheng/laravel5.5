<?php

namespace App\Http\Controllers\Common;

use App\Bll\Common\Account\AccountManage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AccountController extends Controller
{
    /**
     * 登录用户实例
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $LoginUser;
    
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    /**
     * @param Request $request
     * 修改当前用户的管理密码
     * @return \App\Http\Controllers\引发一个http请求的错误异常|\App\Http\Controllers\返回一个response的对像
     */
    public function updatePassword(Request $request) {
        $validate = \Validator::make($request->all(), [
            'u_name' => 'required|string',
            'password' => 'required|string',
            'newpassword' => 'required|string',
            'verifypassword' => 'required|string'
        ]);
        if($validate->fails())
        {
            return $this->response()->error($validate->errors()->all(),-200);
        }
        if($request->newpassword !== $request->verifypassword) {
            return $this->response()->error('新密码重复密码校验失败',-200);
        }
        $this->bindingUser();
        $account = new AccountManage($this->LoginUser);
        $res = $account->updatePassword($request->all());
        if($res) {
            return $this->response()->success('密码修改完成,请重新登录。');
        }else{
            return $this->response()->error($res,-200);
        }
    }
}

<?php
/**
 * 数据库操作demo
 */
namespace App\Http\Controllers;

use App\Model\Test;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //获取列表
//        $lists = Test::where('active', 1)//查询
//            ->orderBy('u_id', 'desc')//排序
//            ->paginate(15);
        $lists = Test::paginate(15);//分页
        $lists = Test::all();//获取所有
        return $this->response()->success($lists);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //跳转添加view
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //保存
        $name = $request->name;
        $test = new Test();
        $test->name = $name;
        try{
            $test->save();
            return $this->response([],0,'保存成功');
        }catch(\Exception $exception) {
            return $this->response([],1,$exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\Test  $test
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //获取某个数据
        return Test::findOrFail($id);//没找到会抛出异常
        return Test::find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Test  $test
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //跳转修改视图
    }

    /**
     * Update the specified resource in storage.
     * put 和patch提交接口 相似 但是patch 通常被用来更新资源
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Test  $test
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //保存编辑信息
        $name = $request->name;
        $test = Test::where('u_id',$id);
        try{
            $test->update(['name'=>$name]);
            return $this->response([],0,'更新成功');
        }catch(\Exception $exception) {
            return $this->response([],1,$exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Test  $test
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //删除
        try{
            Test::destroy($id);
            return $this->response([],0,'删除成功');
        }catch(\Exception $exception) {
            return $this->response([],1,$exception->getMessage());
        }
       
    }
}

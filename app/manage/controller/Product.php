<?php
namespace app\manage\controller;
use think\Db;
use think\Request;
use app\manage\model\ProductManage as Pm;

class Product extends Common
{

  
    public function list(Request $request)
    {
        $param = $request->param();
        $w = [];
        if($request->has('name')) {
            $w['product_name'] = ['like',"%{$request->get('name')}%"];
        }
        $sort_type_of = '';
        if($request->has('sort')) {
            $sort_type = $request->get('sort');
            $sort_type_of = $sort_type==1 ? 'ASC' : 'DESC';
        }
      
        $pm = new Pm;
        if($w) {
          $pm = $pm->where($w);
        }
        if($sort_type_of) {
            $pm = $pm->order('create_at',$sort_type_of);
        }
        $list = $pm->paginate(5,false,['query'=>request()->param()]);
        $this->assign('list',$list);
        return $this->fetch();
    }


    public function edit(Request $request)
    {
        if($request->isGet()){
            $token = $request->get('token');
            if(!$the = Pm::get($token)) {
                $this->error('该产品不存在');
            }
            $this->assign('the',$the);
            return $this->fetch();
        } else {
          
        }
    }
  
    public function add(Request $request)
    {
        if($request->isGet()){
            return $this->fetch();
        } else {
            $res = ['code'=>0,'msg'=>'操作失败'];
            $param = $request->post();
            $book_m = new Pm;
            $full_data = [
                'thumb'  =>  $param['thumb'],
                'product_name'  =>  $param['title'],
                'pdf_path'  =>  $param['pdf_path'],
                'create_at'=>  date('Y-m-d H:i:s')
            ];
          if($request->has('describe')) {
            $full_data['describe'] = $param['describe'];
          }
            if($request->has('product_id')){ // 修改
                unset($full_data['create_at']);
                $full_data['update_at'] = date('Y-m-d H:i:s');
                $book_m->save($full_data,['product_id'=>$param['product_id']]);
            } else {  // 添加
                $book_m->data($full_data);
            }
            $r = $book_m->save();
            // dump($r);die;
            if($r && !$request->has('product_id')) { 
                $res['code'] = 1;
                $res['msg'] = '操作成功';
                $res['url'] = '/manage/product/list';
            } else if($request->has('product_id')) {
                $res['code'] = 1;
                $res['msg'] = '操作成功';
                $res['url'] = '/manage/product/list';
            }
            return $res;
        }
    }


    public function del(Request $request) {
        $param = $request->param();
        $img_id = $param['token'];
        $img_m = new Pm;
        $img_exists = $img_m->where(['product_id'=>$img_id])->find();
        if(!$img_exists) {
            $this->error('此产品不存在或已被删除',$_SERVER['HTTP_REFERER']);
        } else {
            if(Pm::where(['product_id'=>$img_id])->delete()) {
                @unlink($_SERVER['DOCUMENT_ROOT'].'/resource/guzz_resource/'.$img_exists->thumb);
                @unlink($_SERVER['DOCUMENT_ROOT'].'/resource/guzz_pdf/'.$img_exists->pdf_path);
                $this->success('操作成功',$_SERVER['HTTP_REFERER']);
            }
             
        }

    }
}
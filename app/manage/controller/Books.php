<?php
namespace app\manage\controller;
use think\Db;
use think\Request;
use app\common\model\Book;
use app\common\model\Cate;
use app\common\model\Img;

class Books extends Common
{

    public function list(Request $request)
    {
        $book_list_m = DB::name('bookshelf')
        ->alias('b')
        ->join('book_cate c','b.book_cate = c.cate_id')
        ->field('b.*,c.name as cate_name');
        
        if($request->get('book_cate')) {
            $book_cate = $request->get('book_cate');
            $book_list_m = $book_list_m->where('b.book_cate',$book_cate);
        }
// dump($book_list_m);die;
        if($request->get('book_name')) {
            $book_name = $request->get('book_name');
            $book_list = $book_list_m->where('b.title','like','%'.$book_name.'%')->order('b.book_id','ASC')->paginate(5);
        } else {
            $book_list = $book_list_m->order('b.book_id','ASC')->paginate(5);
        }
        $cate_m = new Cate;
        $cate_list = $cate_m->order('s','asc')->select();
        $this->assign('list',$book_list);
        $this->assign('cates',$cate_list);
        return $this->fetch();
    }
    

    public function add(Request $request)
    {
        if($request->isPost()) {
            $res = ['code'=>0,'msg'=>'操作失败'];
            $param = $request->post();
            $book_m = new Book;
            $full_data = [
                'cover'  =>  $param['cover'],
                'title'  =>  $param['title'],
                'en_title'=> $param['en_title'],
                'tel'    =>  $param['tel'],
                'weibo'  =>  $param['weibo'],
                'book_cate'  =>  $param['book_cate'],
                'addtime'=>  time(),
                'page'   =>  0,
                'desc'   =>  $param['desc']
            ];
            if($request->has('book_id')){ // 修改
                $book_m->save($full_data,['book_id'=>$param['book_id']]);
            } else {  // 添加
                $book_m->data($full_data);
            }
            $r = $book_m->save();
            // dump($r);die;
            if($r && !$request->has('book_id')) { 
                $res['code'] = 1;
                $res['msg'] = '操作成功';
                $res['url'] = '/manage/books/list';
            } else if($request->has('book_id')) {
                $res['code'] = 1;
                $res['msg'] = '操作成功';
                $res['url'] = '/manage/books/list';
            }
            return $res;
        } else {
            $cate_m = new Cate;
            $this->assign('cates', $cate_m->order('s','asc')->select());
            return $this->fetch();
        }
        
    }

    public function edit($token)
    {
        $book_data = Book::get($token);
        if(!$book_data) $this->error('数据不存在');
        $cate_m = new Cate;
        $this->assign('cates', $cate_m->order('s','asc')->select());
        $this->assign('data',$book_data);
        return $this->fetch();
    }

    public function del(Request $request)
    {

        if(!$request->has('token')) $this->error('参数有误');
        $the = Book::get($request->param('token'));
        if(!$the) $this->error('该书本不存在');
        $img_m = new Img;
        $img_l = $img_m->where('books_id',$the->book_id)->field('src,img_id')->select();
        if($img_l) {
            foreach ($img_l as $z) {
                @unlink($_SERVER['DOCUMENT_ROOT'].'/resource/book_content/'.$z->src);
            }
        }
        @unlink($_SERVER['DOCUMENT_ROOT'].'/resource/book_resource/'.$the->cover);
        if($the->delete()) $this->success('删除成功','/manage/books/list');
    }

    // public function index()
    // {
    //     $alls = DB::name('negative')
    //     ->alias('n')
    //     ->join('time_wechat_user u','n.wechat_user_id = u.id')
    //     ->join('time_odcore o','n.dd_id = o.id')
    //     ->order('n.create_time','desc')
    //     ->field('n.*,o.order_status,order_no,u.nickname,openid,realname')
    //     ->select();
    //     // dump($alls);
    //     // die;
    //     // dump($alls->toArray());die;
        
        
    //     $this->assign('alls',$alls);
    //     return $this->fetch('show');
    // }

}

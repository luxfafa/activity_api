<?php
namespace app\manage\controller;
use think\Db;
use think\Request;
use app\manage\model\CustomerManage;

class Customer extends Common
{

    public function list(Request $request)
    { 
      $param = $request->param();
        $w = [];
        if($request->has('name')) {
            $w['customer_name'] = ['like','%'.$request->get('name').'%'];
        }
        $sort_type_of = 'ASC';
        if($request->has('sort')) {
            $sort_type = $request->get('sort');
            $sort_type_of = $sort_type==1 ? 'ASC' : 'DESC';
        }
        $pm = new CustomerManage;
        if($w) {
          $pm = $pm->where($w);
        }

        $list = $pm->order('create_at',$sort_type_of)->paginate(10,false,['query'=>request()->param()]);
        $this->assign('list',$list);
        return $this->fetch();
    }
    

    public function add(Request $request)
    {
        if($request->isPost()) {
            $res = ['code'=>0,'msg'=>'操作失败'];
            $param = $request->post();
            $cate_m = new CustomerManage;
            $full_data = [
                'name'    =>  $param['name'],
                'addtime' =>  time(),
                's'       =>$param['s']  // 排序
            ];
            if($request->has('cate_id')){ // 修改
                $cate_m->save($full_data,['cate_id'=>$param['cate_id']]);
            } else {
                $cate_m->data($full_data);
            }
            $r = $cate_m->save();
            if($r && !$request->has('cate_id')) { 
                $res['code'] = 1;
                $res['msg'] = '操作成功';
                $res['url'] = '/manage/customer/list';
            } else if($request->has('cate_id')) {
                $res['code'] = 1;
                $res['msg'] = '操作成功';
                $res['url'] = '/manage/customer/list';
            }
            return $res;
        } else {
            return $this->fetch();
        }
        
    }

    public function edit(Request $request)
    {   
        $token = $request->get('token');
        $cate_data = CustomerManage::get($token);
        if(!$cate_data) $this->error('数据不存在');
        $this->assign('data',$cate_data);
        return $this->fetch();
    }

    public function del(Request $request)
    {
        if(!$request->has('token')) $this->error('参数有误');
        $cate_id = $request->param('token');
        $the = CustomerManage::get($cate_id);
        if(!$the) $this->error('该产品不存在');
        if($the->delete()) $this->success('删除成功','/manage/customer/list');
        $this->error('操作失败');
    }
  
    public function export(Request $request)
    {   
        //导出
        $path = dirname(__FILE__); //找到当前脚本所在路径
        vendor("PHPExcel.PHPExcel.PHPExcel");
        vendor("PHPExcel.PHPExcel.Writer.IWriter");
        vendor("PHPExcel.PHPExcel.Writer.Abstract");
        vendor("PHPExcel.PHPExcel.Writer.Excel5");
        vendor("PHPExcel.PHPExcel.Writer.Excel2007");
        vendor("PHPExcel.PHPExcel.IOFactory");
        $objPHPExcel = new \PHPExcel();
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);


        // 实例化完了之后就先把数据库里面的数据查出来
        $param = $request->param();
        $w = [];
        if($request->has('name')) {
            $w['customer_name'] = ['like','%'.$request->get('name').'%'];
        }
        $sort_type_of = 'ASC';
        if($request->has('sort')) {
            $sort_type = $request->get('sort');
            $sort_type_of = $sort_type==1 ? 'ASC' : 'DESC';
        }
        $pm = new CustomerManage;
        if($w) {
          $pm = $pm->where($w);
        }

        $sql = $pm->order('create_at',$sort_type_of)->select();
      

        // 设置表头信息
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '公司名称')
        ->setCellValue('B1', '联系人')
        ->setCellValue('C1', '联系电话')
        ->setCellValue('D1', '微信号')
        ->setCellValue('E1', '公司行业')
        ->setCellValue('F1', '公司地址')
        ->setCellValue('G1', '邮箱');

        /*--------------开始从数据库提取信息插入Excel表中------------------*/

        $i=2;  //定义一个i变量，目的是在循环输出数据是控制行数
        $count = count($sql);  //计算有多少条数据
        for ($i = 2; $i <= $count+1; $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $sql[$i-2]['company_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $sql[$i-2]['customer_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $sql[$i-2]['mobile']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $sql[$i-2]['wechat_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $sql[$i-2]['company_scale']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $sql[$i-2]['company_address'].' '.$sql[$i-2]['company_address_detail']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $sql[$i-2]['email']);
        }

        
        /*--------------下面是设置其他信息------------------*/

        $objPHPExcel->getActiveSheet()->setTitle('customer_list');      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');   //通过PHPExcel_IOFactory的写函数将上面数据写出来
        
        $PHPWriter = \PHPExcel_IOFactory::createWriter( $objPHPExcel,"Excel2007");
            
        header('Content-Disposition: attachment;filename="客户列表'.time().'".xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        $PHPWriter->save("php://output"); //表示在$path路径下面生成demo.xlsx文件
        
    }
    public function export_row(Request $request)
    {   
        //导出
        if(!$token = $request->get('token')) {
            $this->error('参数有误');
        }
        $sql = CustomerManage::where(['customer_id'=>$token])->find();
        if(!$sql) {
            $this->error('次数据不存在或已删除');
        }
        $path = dirname(__FILE__); //找到当前脚本所在路径
        vendor("PHPExcel.PHPExcel.PHPExcel");
        vendor("PHPExcel.PHPExcel.Writer.IWriter");
        vendor("PHPExcel.PHPExcel.Writer.Abstract");
        vendor("PHPExcel.PHPExcel.Writer.Excel5");
        vendor("PHPExcel.PHPExcel.Writer.Excel2007");
        vendor("PHPExcel.PHPExcel.IOFactory");
        $objPHPExcel = new \PHPExcel();
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
      

        // 设置表头信息
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '公司名称')
        ->setCellValue('B1', '联系人')
        ->setCellValue('C1', '联系电话')
        ->setCellValue('D1', '微信号')
        ->setCellValue('E1', '公司行业')
        ->setCellValue('F1', '公司地址')
        ->setCellValue('G1', '邮箱');
// dump($sql);die;
        /*--------------开始从数据库提取信息插入Excel表中------------------*/

        $i=2;  //定义一个i变量，目的是在循环输出数据是控制行数
        $b = 0; // 单条数据

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $sql['company_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $sql['customer_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $sql['mobile']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $sql['wechat_id']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $sql['company_scale']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $sql['company_address'].' '.$sql['company_address_detail']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $sql['email']);

        
        /*--------------下面是设置其他信息------------------*/

        $objPHPExcel->getActiveSheet()->setTitle('customer_list');      //设置sheet的名称
        $objPHPExcel->setActiveSheetIndex(0);                   //设置sheet的起始位置
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');   //通过PHPExcel_IOFactory的写函数将上面数据写出来
        
        $PHPWriter = \PHPExcel_IOFactory::createWriter( $objPHPExcel,"Excel2007");
            
        header('Content-Disposition: attachment;filename="客户列表'.time().'".xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        $PHPWriter->save("php://output"); //表示在$path路径下面生成demo.xlsx文件
        
    }
}
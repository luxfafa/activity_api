<?php
namespace app\manage\controller;
use think\Session;
use think\Controller;
use think\Request;
class Common extends Controller
{
    public function _initialize()
	{
		if(!Session::has('_supportuser')){
    		$this->error('请登录','/manage/user/login');
    	}
        $uinfo = Session::get('_supportuser');
	}
    /**
     * 上传图片资源
    */
	public function upload_resource(Request $request)
	{
        $requestType = $request->method();
        if($requestType=='POST') {
            $efile = $request->file('thumb');
            if(!$efile) {
                $this->error('缩略图未上传!');
            }
            $resource_path = __APP__ . 'resource/guzz_thumb';
            $upload_info = $efile->move($resource_path);
            if($upload_info) {
                $this->success('缩略图上传成功','',$upload_info->getSaveName());
            } else {
                $this->error('缩略图上传失败');
            }

        }
	}

    /**
     * 上传PDF资源
    */
	public function upload_pdf(Request $request)
	{
        $requestType = $request->method();
        if($requestType=='POST') {
            $efile = $request->file('pdf_file');
            if(!$efile) {
                $this->error('PDF未上传!');
            }
            $resource_path = __APP__ . 'resource/guzz_pdf';
            $upload_info = $efile->move($resource_path);
            if($upload_info) {
                $this->success('PDF上传成功','',$upload_info->getSaveName());
            } else {
                $this->error('PDF上传失败');
            }

        }
    }

}
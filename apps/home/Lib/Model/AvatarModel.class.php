<?php
/** 头像设置 **/
class AvatarModel extends Model{
	var $uid;

    function getSavePath(){
		//使用SAE上传
		if(C('TS_STORAGE_TYPE')=='SAEST'){
			$savePath = 'avatar-'.$this->uid;
		}else{
			$savePath = SITE_PATH.'/data/uploads/avatar'.convertUidToPath($this->uid);
			if( !file_exists( $savePath ) ) mk_dir( $savePath  );
        }
		return $savePath;
    }

    //将远程图片转换成本地头像
    public function saveAvatar($uid,$faceurl)
    {
    	$this->uid = $uid;
		//使用SAE上传
		if(C('TS_STORAGE_TYPE')=='SAEST'){
			$original = $this->getSavePath()."-original.jpg";
			$big   = $this->getSavePath()."-big.jpg";
			$middle = $this->getSavePath()."-middle.jpg";
			$small = $this->getSavePath()."-small.jpg";

			//获取远程图片数据
			$img_data = file_get_contents($faceurl);

			if(!$img_data) return false;

			//输出原图
			$s = new SaeStorage();
			$s->write(C('TS_STORAGE_DOMAIN'), $original , $img_data);

			$img = new SaeImage();

			//输出大图
			$img->setData( $img_data );
			$img->resize(150);
			$img->improve();
			$new_data = $img->exec('jpg');
			$s->write(C('TS_STORAGE_DOMAIN'), $big , $new_data);

			//输出中图
			$img->setData( $new_data );
			$img->resize(50);
			$img->improve();
			$new_data = $img->exec('jpg');
			$s->write(C('TS_STORAGE_DOMAIN'), $middle , $new_data);

			//输出小图
			$img->setData( $new_data );
			$img->resize(30);
			$img->improve();
			$new_data = $img->exec('jpg');
			$s->write(C('TS_STORAGE_DOMAIN'), $small , $new_data);

		}else{
			$original = $this->getSavePath()."/original.jpg";
			$big   = $this->getSavePath()."/big.jpg";
			$middle = $this->getSavePath()."/middle.jpg";
			$small = $this->getSavePath()."/small.jpg";
			include( SITE_PATH.'/addons/libs/Image.class.php' );
			Image::thumb( $faceurl, $original, '', 180, 180);
			Image::thumb( $faceurl, $big, '', 150, 150);
			Image::thumb( $faceurl, $middle, '', 50, 50);
			Image::thumb( $faceurl, $small, '', 30, 30);
		}
	}

    //上传头像
    function upload(){
        @header("Expires: 0");
        @header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");
        $pic_id = time();//使用时间来模拟图片的ID.
        $pic_path = $this->getSavePath().'/original.jpg';
        $pic_abs_path = __UPLOAD__.'/avatar'.convertUidToPath($this->uid).'/original.jpg';
        //保存上传图片.
        if(empty($_FILES['Filedata'])) {
        	$return['message'] = L('photo_upload_failed');
        	$return['code']    = '0';
        }else{
			if(C('TS_STORAGE_TYPE')=='SAEST'){
				$face = X('Xattach')->upload('weibo_temp');
				if(!$face['status']){
					$return['code']    = 0;
					$return['message']   = L('photo_upload_failed');
				}else{
					$return['code']    = 1;
					$return['data']['picurl'] = UPLOAD_URL.'/'.$face['info'][0]['savename'];
					if($_FILES['Filedata']['tmp_name']){
						list($sr_w, $sr_h, $sr_type, $sr_attr) = @getimagesize($_FILES['Filedata']['tmp_name']);
						$return['data']['picwidth'] = $sr_w;
						$return['data']['picheight'] = $sr_h;
					}
				}
			}else{
				$file = @$_FILES['Filedata']['tmp_name'];
				file_exists($pic_path) && @unlink($pic_path);
				if(@copy($_FILES['Filedata']['tmp_name'], $pic_path) || @move_uploaded_file($_FILES['Filedata']['tmp_name'], $pic_path))
				{
					@unlink($_FILES['Filedata']['tmp_name']);
					/*list($width, $height, $type, $attr) = getimagesize($pic_path);
					if($width < 10 || $height < 10 || $width > 3000 || $height > 3000 || $type == 4) {
						@unlink($pic_path);
						return -2;
					}*/
					include( SITE_PATH.'/addons/libs/Image.class.php' );
					Image::thumb( $pic_path, $pic_path , '' , 300 , 300 );
					list($sr_w, $sr_h, $sr_type, $sr_attr) = @getimagesize($pic_path);

					$return['data']['picurl'] = 'data/uploads/avatar'.convertUidToPath($this->uid).'/original.jpg';
					$return['data']['picwidth'] = $sr_w;
					$return['data']['picheight'] = $sr_h;
					$return['code']    = '1';
				} else {
					@unlink($_FILES['Filedata']['tmp_name']);
					$return['message'] = L('photo_upload_failed');
					$return['code']    = '0';
				}
			}
        }
        return json_encode( $return );
    }

    //保存图片
    function dosave($uid){
    	//header("Content-type: image/jpeg");
		$x1 = (int) $_POST['x1'];//客户端选择区域左上角x轴坐标
		$y1 = (int) $_POST['y1'];//客户端选择区域左上角y轴坐标
		$x2 = (int) $_POST['x2'];//客户端选择区 的宽
		$y2 = (int) $_POST['y2'];//客户端选择区 的高
		$w = (int) $_POST['w'];//客户端选择区 的高
		$h = (int) $_POST['h'];//客户端选择区 的高

		if(C('TS_STORAGE_TYPE')=='SAEST'){

			$bigpicfile = basename($_POST['picurl']);

			$original = $this->getSavePath()."-original.jpg";
			$big   = $this->getSavePath()."-big.jpg";
			$middle = $this->getSavePath()."-middle.jpg";
			$small = $this->getSavePath()."-small.jpg";

			//获取远程图片数据
			//执行缩图
			$s = new SaeStorage();
			$img_data = $s->read(C('TS_STORAGE_DOMAIN'),$bigpicfile);

			if(!$img_data) return false;

			$img = new SaeImage();

			//输出原图
			$img->setData( $img_data );
			$imginfo = $img->getImageAttr();
			$x = $imginfo[0];
			$y = $imginfo[1];
			$img->crop($x1/$x,$x2/$x,$y2/$y,$y1/$y);
			//$img->resize(180);
			//$img->improve();
			$new_data = $img->exec('jpg');
			$s->delete(C('TS_STORAGE_DOMAIN'), $original);
			$s->write(C('TS_STORAGE_DOMAIN'), $original , $new_data);

			//输出大图
			$img->clean();
			$img->setData( $new_data );
			$img->resize(150);
			//$img->improve();
			$new_data = $img->exec('jpg');
			$s->delete(C('TS_STORAGE_DOMAIN'), $big);
			$s->write(C('TS_STORAGE_DOMAIN'), $big , $new_data);

			//输出中图
			$img->clean();
			$img->setData( $new_data );
			$img->resize(50);
			//$img->improve();
			$new_data = $img->exec('jpg');
			$s->delete(C('TS_STORAGE_DOMAIN'), $middle);
			$s->write(C('TS_STORAGE_DOMAIN'), $middle , $new_data);

			//输出小图
			$img->clean();
			$img->setData( $new_data );
			$img->resize(30);
			//$img->improve();
			$new_data = $img->exec('jpg');
			$s->delete(C('TS_STORAGE_DOMAIN'), $small);
			$s->write(C('TS_STORAGE_DOMAIN'), $small , $new_data);

			echo '1';
		}else{

			$src = SITE_PATH.'/'.$_POST['picurl'];//图片的路径

			// 获取源图的扩展名宽高
			list($sr_w, $sr_h, $sr_type, $sr_attr) = @getimagesize($src);
			if($sr_type){
				//获取后缀名
				$ext = image_type_to_extension($sr_type,false);
			} else {
				echo "-1";
				exit;
			}

			$big_w = '150';
			$big_h = '150';

			$middle_w = '50';
			$middle_h = '50';

			$small_w  = '30';
			$small_h  = '30';

			$face_path      =   SITE_PATH.'/data/uploads/avatar'.convertUidToPath($uid);
			$big_name	    =	$face_path.'/big.jpg';		// 大图
			$middle_name	=	$face_path.'/middle.jpg';		// 中图
			$small_name		=	$face_path.'/small.jpg';

			$func	=	($ext != 'jpg')?'imagecreatefrom'.$ext:'imagecreatefromjpeg';
			$img_r	=	call_user_func($func,$src);

			$dst_r	=	ImageCreateTrueColor( $big_w, $big_h );
			$back	=	ImageColorAllocate( $dst_r, 255, 255, 255 );
			ImageFilledRectangle( $dst_r, 0, 0, $big_w, $big_h, $back );
			ImageCopyResampled( $dst_r, $img_r, 0, 0, $x1, $y1, $big_w, $big_h, $w, $h );

			ImagePNG($dst_r,$big_name);  // 生成大图

			// 开始切割大方块头像成中等方块头像
			$sdst_r	=	ImageCreateTrueColor( $middle_w, $middle_h );
			ImageCopyResampled( $sdst_r, $dst_r, 0, 0, 0, 0, $middle_w, $middle_h, $big_w, $big_w );
			ImagePNG($sdst_r,$middle_name);  // 生成中图


			// 开始切割大方块头像成中等方块头像
			$sdst_s	=	ImageCreateTrueColor( $small_w, $small_h );
			ImageCopyResampled( $sdst_s, $dst_r, 0, 0, 0, 0, $small_w, $small_h, $big_w, $big_w );
			ImagePNG($sdst_s,$small_name);  // 生成中图

			ImageDestroy($dst_r);
			ImageDestroy($sdst_r);
			ImageDestroy($sdst_s);
			ImageDestroy($img_r);
			echo '1';
		}
    }

    function getcamera(){
        @header("Expires: 0");
        @header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");

        $pic_id = time();

        //生成图片存放路径
        $new_avatar_path = $this->getSavePath().'/original.jpg';

        //将POST过来的二进制数据直接写入图片文件.
        $len = file_put_contents($this->getSavePath().'/original.jpg',file_get_contents("php://input"));

        //原始图片比较大，压缩一下. 效果还是很明显的, 使用80%的压缩率肉眼基本没有什么区别
        //$avtar_img = imagecreatefromjpeg($new_avatar_path);
       // imagejpeg($avtar_img,$new_avatar_path,80);
        //nix系统下有必要时可以使用 chmod($filename,$permissions);

        //输出新保存的图片位置, 测试时注意改一下域名路径, 后面的statusText是成功提示信息.
        //status 为1 是成功上传，否则为失败.
        $d = new pic_data();
        $d->data->photoId = $pic_id;
        //$d->data->urls[0] = 'http://sns.com/avatar_test/'.$new_avatar_path;
        $d->data->urls[0] = __UPLOAD__.'/avatar'.convertUidToPath($this->uid).'/original.jpg';
        $d->status = 1;
        $d->statusText = L('upload_success');

        $msg = json_encode($d);

        echo $msg;
    }
}

class pic_data
{
	 public $data;
	 public $status;
	 public $statusText;
	public function __construct()
	{
		$this->data->urls = array();
	}
}
?>
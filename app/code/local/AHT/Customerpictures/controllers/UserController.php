<?php
class AHT_Customerpictures_UserController extends Mage_Core_Controller_Front_Action
{
    private function _redirectLoginForm(){
		if((!Mage::helper('customer')->isLoggedIn()) || !Mage::getStoreConfig('customerpictures/general/enabled')){
			$url = Mage::getUrl('customer/account/');
			$this->_redirectUrl($url);
			return;
		}
	}
	
	
	private function _getCustomer(){
		return Mage::getSingleton('customer/session');
	}
	
	public function indexAction()
    {
		$this->_redirectLoginForm();
		$_customer = $this->_getCustomer();
		$lookAtYouUser = Mage::getModel('customerpictures/users')->load($_customer->getId());
		if($lookAtYouUser->getId()!=''){
			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle($this->__('Customer pictures user home'));
			$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
			$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Home Page'), 'link'=>Mage::getBaseUrl()));
			$breadcrumbs->addCrumb('account', array('label'=>Mage::helper('cms')->__('My account'), 'title'=>Mage::helper('cms')->__('My account'), 'link'=>Mage::getUrl('customer/account')));
			$breadcrumbs->addCrumb('customerpictures', array('label'=>'Customer pictures', 'title'=>'Customer pictures'));
			$this->renderLayout();
		}
		else{
			$url = Mage::getUrl('customerpictures/user/terms');
			$this->_redirectUrl($url);
			return;
		}
    }
	
	
	//Term and condition page
	public function termsAction(){
		$this->_redirectLoginForm();
		
		$_customer = $this->_getCustomer();
		$lookAtYouUserCollection = Mage::getModel('customerpictures/users')
			->getCollection()
			->addFieldToFilter('user_id', $_customer->getId())
			->addFieldToFilter('term_condition', 1);
		if(count($lookAtYouUserCollection)>0){
			$url = Mage::getUrl('customerpictures/user');
			$this->_redirectUrl($url);
			return;
		}
		
		
		$this->loadLayout();   
		$this->getLayout()->getBlock('head')->setTitle($this->__('Customer pictures - Term of use'));
		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Home Page'), 'link'=>Mage::getBaseUrl()));
		$breadcrumbs->addCrumb('account', array('label'=>Mage::helper('cms')->__('My account'), 'title'=>Mage::helper('cms')->__('My account'), 'link'=>Mage::getUrl('customer/account')));
		$breadcrumbs->addCrumb('customerpictures', array('label'=>Mage::helper('cms')->__('Customer pictures'), 'title'=>Mage::helper('cms')->__('Customer pictures'), 'link'=>Mage::getUrl('customerpictures/user')));
		$breadcrumbs->addCrumb('term', array('label'=>'Term of use', 'title'=>'Term of use'));
		$this->renderLayout();
	}
	
	public function acceptAction(){
		$this->_redirectLoginForm();
		$dataPost = $this->getRequest()->getPost();
		$model = Mage::getModel('customerpictures/users');
		$data['user_id'] = $this->_getCustomer()->getId();
		$data['term_condition'] = $dataPost['terms_conditions'];
		$model->setData($data)->save();
		
		$url = Mage::getUrl('customerpictures/user');
		$this->_redirectUrl($url);
		return;
	}
	
	
	public function avatarAction(){
		// list of valid extensions, ex. array("jpeg", "xml", "bmp")
		$allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
		// max file size in bytes
		$sizeLimit = Mage::getStoreConfig('customerpictures/general/limit') * 1024 * 1024;
		$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
		$result = $uploader->handleUpload('avatars', $this->_getCustomer()->getId());
		// to pass data through iframe you will need to encode all html tags
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
		
	}
	
	public function imagesAction(){
		// list of valid extensions, ex. array("jpeg", "xml", "bmp")
		$allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
		// max file size in bytes
		$sizeLimit = Mage::getStoreConfig('customerpictures/general/limit') * 1024 * 1024;
		$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
		$result = $uploader->handleUpload('images', $this->_getCustomer()->getId());
		// to pass data through iframe you will need to encode all html tags
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
		
	}
	
	public function tempAction(){
		$this->_redirectLoginForm();
		if($fileName = Mage::getSingleton('core/session')->getImageName()){
			$customerId = $this->_getCustomer()->getId();
			$imageUrl = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$customerId.DS.$imageName;
			if(file_exists($imageUrl)){
				$this->loadLayout();   
				$this->getLayout()->getBlock('head')->setTitle($this->__('Customer pictures - Upload a picture'));
				$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
				$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Home Page'), 'link'=>Mage::getBaseUrl()));
				$breadcrumbs->addCrumb('account', array('label'=>Mage::helper('cms')->__('My account'), 'title'=>Mage::helper('cms')->__('My account'), 'link'=>Mage::getUrl('customer/account')));
				$breadcrumbs->addCrumb('customerpictures', array('label'=>Mage::helper('cms')->__('Customer pictures'), 'title'=>Mage::helper('cms')->__('Customer pictures'), 'link'=>Mage::getUrl('customerpictures/user')));
				$breadcrumbs->addCrumb('temp', array('label'=>'Upload a picture', 'title'=>'Upload a picture'));
				$this->renderLayout();
			}
			else{
				$url = Mage::getUrl('customerpictures/user/');
				$this->_redirectUrl($url);
			}
		}
		else{
			$url = Mage::getUrl('customerpictures/user/');
			$this->_redirectUrl($url);
		}
	}
	
	public function editpictureAction(){
		$this->_redirectLoginForm();
		if($id = $this->getRequest()->getParam('id')){
			$image = Mage::getModel('customerpictures/images')->load($id);
			$customerId = $this->_getCustomer()->getId();
			$imageUrl = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$customerId.DS.$image->getImageName();
			if(file_exists($imageUrl)){
				$this->loadLayout();   
				$this->getLayout()->getBlock('head')->setTitle($this->__('Customer pictures - Edit a picture'));
				$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
				$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Home Page'), 'link'=>Mage::getBaseUrl()));
				$breadcrumbs->addCrumb('account', array('label'=>Mage::helper('cms')->__('My account'), 'title'=>Mage::helper('cms')->__('My account'), 'link'=>Mage::getUrl('customer/account')));
				$breadcrumbs->addCrumb('customerpictures', array('label'=>Mage::helper('cms')->__('Customer pictures'), 'title'=>Mage::helper('cms')->__('Customer pictures'), 'link'=>Mage::getUrl('customerpictures/user')));
				$breadcrumbs->addCrumb('temp', array('label'=>'Upload a picture', 'title'=>'Edit a picture'));
				$this->renderLayout();
			}
			else{
				$url = Mage::getUrl('customerpictures/user/');
				$this->_redirectUrl($url);
			}
		}
		else{
			$url = Mage::getUrl('customerpictures/user/');
			$this->_redirectUrl($url);
		}
	}
	
	public function editAction(){
		$this->_redirectLoginForm();
		$model = Mage::getModel('customerpictures/images');
		$data = $this->getRequest()->getPost('image');
		if(isset($data['image_id'])){
			$model->load($data['image_id']);
			$model->setImageTitle($data['image_title']);
			$model->setImageDescription($data['image_description']);
			$model->setPositionX($data['position_x'])->setPositionY($data['position_y'])->setPositionW($data['position_w'])->setPositionH($data['position_h']);
			
			$baseDir = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$this->_getCustomer()->getId();

			$urlbackend = $baseDir.DS."resize".DS."80x80".DS.$model->getImageName();
			$userSize = Mage::getStoreConfig('customerpictures/user/width').'x'.Mage::getStoreConfig('customerpictures/user/height');
			$pageSize = Mage::getStoreConfig('customerpictures/page/width').'x'.Mage::getStoreConfig('customerpictures/page/height');
			$viewSize = Mage::getStoreConfig('customerpictures/view/width').'x'.Mage::getStoreConfig('customerpictures/view/height');
			
			$urlResize1 = $baseDir.DS."resize".DS.$userSize.DS.$model->getImageName();
			$urlResize2 = $baseDir.DS."resize".DS.$pageSize.DS.$model->getImageName();
			$urlResize3 = $baseDir.DS."resize".DS.$viewSize.DS.$model->getImageName();
			
			unlink($urlbackend);
			unlink($urlResize1);
			unlink($urlResize2);
			unlink($urlResize3);
		}
		else{
			$data['user_id'] = $this->_getCustomer()->getId();
			$data['created_time'] = time();
			$model->setData($data);
		}
		
		
		
		$_image = 'media/customerpictures/images/'.$this->_getCustomer()->getId().'/'.$data['image_name'];
		$thumb_folder = 'media/customerpictures/images/'.$this->_getCustomer()->getId().'/thumb/';
		
		if(!is_dir($thumb_folder)){
			mkdir($thumb_folder , 0777);
		}
		
		$thumb = $thumb_folder.$data['image_name'];
		
		$objImage = new ImageManipulation($_image);
		if ($objImage->imageok ) {
			$objImage->setCrop($data['position_x'], $data['position_y'], $data['position_w'], $data['position_h']);
			$objImage->save($thumb);
		} else {
			echo 'Error!';
		}
		
		try{
			$model->save();
			Mage::getSingleton('core/session')->setImageName(false);
		}catch (Exception $e) {
			 Mage::getSingleton('core/session')->addError($e->getMessage());
		}
		$url = Mage::getUrl('customerpictures/user/');
		$this->_redirectUrl($url);
	}
	
	public function deleteAction(){
		$this->_redirectLoginForm();
		$imageName = $this->getRequest()->getParam('image');
		$url = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$this->_getCustomer()->getId().DS.$imageName;
		unlink($url);
	}
	
	public function hideAction(){
		$this->_redirectLoginForm();
		$id = $this->getRequest()->getParam('id');
		$image = Mage::getModel('customerpictures/images')->load($id);
		$image->setUserStatus(1)->save();
		Mage::getSingleton('core/session')->addSuccess(Mage::helper('customerpictures')->__('The picture was successfully hided'));
		$url = Mage::getUrl('customerpictures/user/');
		$this->_redirectUrl($url);
	}
	
	public function showAction(){
		$this->_redirectLoginForm();
		$id = $this->getRequest()->getParam('id');
		$image = Mage::getModel('customerpictures/images')->load($id);
		$image->setUserStatus(0)->save();
		Mage::getSingleton('core/session')->addSuccess(Mage::helper('customerpictures')->__('The picture was successfully showed'));
		$url = Mage::getUrl('customerpictures/user/');
		$this->_redirectUrl($url);
	}
	
	public function delAction(){
		$this->_redirectLoginForm();
		$id = $this->getRequest()->getParam('id');
		$image = Mage::getModel('customerpictures/images')->load($id);
		
		$baseDir = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$this->_getCustomer()->getId();
		
		$url = $baseDir.DS.$image->getImageName();
		$urlbackend = $baseDir.DS."resize".DS."80x80".DS.$image->getImageName();
		$userSize = Mage::getStoreConfig('customerpictures/user/width').'x'.Mage::getStoreConfig('customerpictures/user/height');
		$pageSize = Mage::getStoreConfig('customerpictures/page/width').'x'.Mage::getStoreConfig('customerpictures/page/height');
		$viewSize = Mage::getStoreConfig('customerpictures/view/width').'x'.Mage::getStoreConfig('customerpictures/view/height');
		
		$urlThumb = $baseDir.DS."thumb".DS.$image->getImageName();
		$urlResize1 = $baseDir.DS."resize".DS.$userSize.DS.$image->getImageName();
		$urlResize2 = $baseDir.DS."resize".DS.$pageSize.DS.$image->getImageName();
		$urlResize3 = $baseDir.DS."resize".DS.$viewSize.DS.$image->getImageName();
		
		unlink($url);
		unlink($urlThumb);
		unlink($urlbackend);
		unlink($urlResize1);
		unlink($urlResize2);
		unlink($urlResize3);
		
		$image->delete();
		
		Mage::getSingleton('core/session')->addSuccess(Mage::helper('customerpictures')->__('The picture was successfully deleted'));
		$url = Mage::getUrl('customerpictures/user/');
		$this->_redirectUrl($url);
	}
	
	public function cancelAction(){
		$this->_redirectLoginForm();
		$imageCollection = Mage::getModel('customerpictures/images')->getCollection()
			->addFieldToFilter('user_id', $this->_getCustomer()->getId())
			->addFieldToFilter('winner_time', array('eq' => ''));
		$size = count($imageCollection);
		if($size>0){
			foreach($imageCollection as $_image){
			
				$image = Mage::getModel('customerpictures/images')->load($_image->getId());
				$imageDir = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$this->_getCustomer()->getId();
				$url = $imageDir.DS.$image->getImageName();
				$urlbackend = $imageDir.DS."resize".DS."80x80".DS.$image->getImageName();
				
				$userSize = Mage::getStoreConfig('customerpictures/user/width').'x'.Mage::getStoreConfig('customerpictures/user/height');
				$pageSize = Mage::getStoreConfig('customerpictures/page/width').'x'.Mage::getStoreConfig('customerpictures/page/height');
				$viewSize = Mage::getStoreConfig('customerpictures/view/width').'x'.Mage::getStoreConfig('customerpictures/view/height');
				
				$urlThumb = $imageDir.DS."thumb".DS.$image->getImageName();
				$urlResize1 = $imageDir.DS."resize".DS.$userSize.DS.$image->getImageName();
				$urlResize2 = $imageDir.DS."resize".DS.$pageSize.DS.$image->getImageName();
				$urlResize3 = $imageDir.DS."resize".DS.$viewSize.DS.$image->getImageName();

				try{
					unlink($url);
					unlink($urlThumb);
					unlink($urlbackend);
					unlink($urlResize1);
					unlink($urlResize2);
					unlink($urlResize3);
					Mage::getModel('customerpictures/images')->load($image->getId())->delete();
				} catch (Exception $e) {
					Mage::getSingleton('core/session')->addError($e);
				}
			}
		}
		$user = Mage::getModel('customerpictures/users')->load($this->_getCustomer()->getId());
		$avatarName = $user->getAvatar();
		$avatarDir = Mage::getBaseDir('media').DS."customerpictures".DS."avatars".DS.$this->_getCustomer()->getId();
		$avatar = $avatarDir.DS.$avatarName;
		$avatarResize = $avatarDir.DS."resize".DS.$avatarName;
		Mage::getModel('customerpictures/users')->load($this->_getCustomer()->getId())->delete();
		unlink($avatar);
		unlink($avatarResize);	
		
		Mage::getSingleton('core/session')->addSuccess(Mage::helper('customerpictures')->__('Your look at you account was successfully deleted'));
		$url = Mage::getUrl('customer/account/');
		$this->_redirectUrl($url);
	}
	
}

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($type, $customerId, $replaceOldFile = FALSE){
		if(!is_dir('media/customerpictures/')){
			mkdir('media/customerpictures/' , 0777);
		}
		
		if(!is_dir('media/customerpictures/avatars/')){
			mkdir('media/customerpictures/avatars/' , 0777);
		}
		
		if(!is_dir('media/customerpictures/images/')){
			mkdir('media/customerpictures/images/' , 0777);
		}
		
		if($type == 'avatars'){
			$uploadDirectory= 'media/customerpictures/avatars/'.$customerId.'/';
		}
		else{
			$uploadDirectory= 'media/customerpictures/images/'.$customerId.'/';
		}
		
		if(!is_dir($uploadDirectory)){
			mkdir($uploadDirectory , 0777);
		}
		
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
			$user = Mage::getModel('customerpictures/users')->load($customerId);	
			$trueFileName =  $filename . '.' . $ext;
			
			
			if($type == 'avatars'){
				$user->setAvatar($trueFileName)->save();
				$url = Mage::getBaseDir('media').DS."customerpictures".DS."avatars".DS.$customerId.DS.$user->getAvatar(); 
				$src = Mage::helper('customerpictures/data')->reSize($url, $user->getAvatar(), 'avatars', $customerId, 115, 130);
			}
            else{
				$src = $trueFileName;
				Mage::getSingleton('core/session')->setImageName($src);
			}
			return array('success'=>true, 'src'=>$src);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}

class ImageManipulation {

	/**
	 * An array to hold the settings for the image. Default values for
	 * images are set here.
	 *
	 * @var array
	 */
	public $image = array('targetx'=>0, 
							'targety'=>0,
							'quality'=>100);
	
	/**
	 * A boolean value to detect if an image has not been created. This
	 * can be used to validate that an image is viable before trying 
	 * resize or crop.
	 *
	 * @var boolean
	 */
	public $imageok = false;
	
    /**
     * Contructor method. Will create a new image from the target file.
	 * Accepts an image filename as a string. Method also works out how
	 * big the image is and stores this in the $image array.
     *
     * @param string $imgFile The image filename.
     */
	public function ImageManipulation($imgfile)
	{
		//detect image format
		$this->image["format"] = ereg_replace(".*\.(.*)$", "\\1", $imgfile);
		$this->image["format"] = strtoupper($this->image["format"]);
		
		// convert image into usable format.
		if ( $this->image["format"] == "JPG" || $this->image["format"] == "JPEG" ) {
			//JPEG
			$this->image["format"] = "JPEG";
			$this->image["src"]    = ImageCreateFromJPEG($imgfile);
		} elseif( $this->image["format"] == "PNG" ){
			//PNG
			$this->image["format"] = "PNG";
			$this->image["src"]    = imagecreatefrompng($imgfile);
		} elseif( $this->image["format"] == "GIF" ){
			//GIF
			$this->image["format"] = "GIF";
			$this->image["src"]    = ImageCreateFromGif($imgfile);
		} elseif ( $this->image["format"] == "WBMP" ){
			//WBMP
			$this->image["format"] = "WBMP";
			$this->image["src"]    = ImageCreateFromWBMP($imgfile);
		} else {
			//DEFAULT
			return false;
		}

		// Image is ok
		$this->imageok = true;
		
		// Work out image size
		$this->image["sizex"]  = imagesx($this->image["src"]);
		$this->image["sizey"] = imagesy($this->image["src"]);
	}

    /**
     * Sets the height of the image to be created. The width of the image
	 * is worked out depending on the value of the height.
     *
     * @param int $height The height of the image.
     */
	public function setImageHeight($height=100)
	{
		//height
		$this->image["sizey_thumb"] = $height;
		$this->image["sizex_thumb"]  = ($this->image["sizey_thumb"]/$this->image["sizey"])*$this->image["sizex"];
	}
	
    /**
     * Sets the width of the image to be created. The height of the image
	 * is worked out depending on the value of the width.
     *
     * @param int $size The width of the image.
     */
	public function setImageWidth($width=100)
	{
		//width
		$this->image["sizex_thumb"]  = $width;
		$this->image["sizey_thumb"] = ($this->image["sizex_thumb"]/$this->image["sizex"])*$this->image["sizey"];
	}

	/**
     * This method automatically sets the width and height depending
	 * on the dimensions of the image up to a maximum value.
     *
     * @param int $size The maximum size of the image.
     */
	public function resize($size=100)
	{
		if ( $this->image["sizex"] >= $this->image["sizey"] ) {
			$this->image["sizex_thumb"]  = $size;
			$this->image["sizey_thumb"] = ($this->image["sizex_thumb"]/$this->image["sizex"])*$this->image["sizey"];
		} else {
			$this->image["sizey_thumb"] = $size;
			$this->image["sizex_thumb"]  = ($this->image["sizey_thumb"]/$this->image["sizey"])*$this->image["sizex"];
		}
	}

	/**
     * This method sets the cropping values of the image. Be sure
	 * to set the height and with of the image if you want the
	 * image to be a certain size after cropping.
     *
     * @param int $x The x coordinates to start cropping from.
     * @param int $y The y coordinates to start cropping from.
	 * @param int $w The width of the crop from the x and y coordinates.
     * @param int $h The height of the crop from the x and y coordinates.
     */
	public function setCrop($x, $y, $w, $h)
	{
		$this->image["targetx"] = $x;
		$this->image["targety"] = $y;
		$this->image["sizex"] = $w;
		$this->image["sizey"] = $h;
	}
	
	/**
     * Sets the JPEG output quality.
     *
     * @param int $quality The quality of the JPEG image.
     */
	public function setJpegQuality($quality=100)
	{
		//jpeg quality
		$this->image["quality"] = $quality;
	}

	/**
     * Shows the image to a browser. Sets the correct image format in a header.
     */
	public function show()
	{
		//show thumb
		header("Content-Type: image/".$this->image["format"]);

		$this->createResampledImage();
		
		if ( $this->image["format"]=="JPG" || $this->image["format"]=="JPEG" ) {
			//JPEG
			imageJPEG($this->image["des"], "", $this->image["quality"]);
		} elseif ( $this->image["format"] == "PNG" ) {
			//PNG
			imagePNG($this->image["des"]);
		} elseif ( $this->image["format"] == "GIF" ) {
			//GIF
			imageGIF($this->image["des"]);
		} elseif ( $this->image["format"] == "WBMP" ) {
			//WBMP
			imageWBMP($this->image["des"]);
		}
	}
	
	/**
     * Private method to run the imagecopyresampled() function with the parameters that have been set up.
	 * This method is used by the save() and show() methods.
     */
	private function createResampledImage()
	{
		/* change ImageCreateTrueColor to ImageCreate if your GD not supported ImageCreateTrueColor function*/
		if ( isset($this->image["sizex_thumb"]) && isset($this->image["sizey_thumb"]) ) {		
			$this->image["des"] = ImageCreateTrueColor($this->image["sizex_thumb"], $this->image["sizey_thumb"]);
			imagecopyresampled($this->image["des"], $this->image["src"], 0, 0, $this->image["targetx"], $this->image["targety"], $this->image["sizex_thumb"], $this->image["sizey_thumb"], $this->image["sizex"], $this->image["sizey"]);
		} else {
			$this->image["des"] = ImageCreateTrueColor($this->image["sizex"], $this->image["sizey"]);
			imagecopyresampled($this->image["des"], $this->image["src"], 0, 0, $this->image["targetx"], $this->image["targety"], $this->image["sizex"], $this->image["sizey"], $this->image["sizex"], $this->image["sizey"]);
		}	
	}
	
	/**
     * Saves the image to a given filename, if no filename is given then a default is created.
	 *
	 * @param string $save The new image filename.
     */	
	public function save($save="")
	{
		//save thumb
		if ( empty($save) ) {
			$save = strtolower("./thumb.".$this->image["format"]);
		}
		header("Content-Type: image/".$this->image["format"]);
		$this->createResampledImage();

		if ( $this->image["format"] == "JPG" || $this->image["format"] == "JPEG" ) {
			//JPEG
			imageJPEG($this->image["des"], $save, $this->image["quality"]);
		} elseif ( $this->image["format"] == "PNG" ) {
			//PNG
			imagePNG($this->image["des"], $save);
		} elseif ( $this->image["format"] == "GIF" ) {
			//GIF
			imageGIF($this->image["des"], $save);
		} elseif ( $this->image["format"] == "WBMP" ) {
			//WBMP
			imageWBMP($this->image["des"], $save);
		}
		
		header("Content-Type: text/html");
	}
}

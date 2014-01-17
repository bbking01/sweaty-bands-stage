<?php

class Magestore_Clipartmanagement_Adminhtml_ClipartController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() { 
		$this->loadLayout()
			->_setActiveMenu('clipartmanagement/clipart')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Clipart Management'), Mage::helper('adminhtml')->__('Clipart Management'));
		
		return $this;
	}   
 
	public function indexAction() {		
		$this->_initAction()
			->renderLayout();		
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('clipartmanagement/clipart')->load($id);
		
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
						
			Mage::register('clipartmanagement_data', $model);
			$this->loadLayout();
			$this->_setActiveMenu('clipartmanagement/clipart');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Clipart Management'), Mage::helper('adminhtml')->__('Clipart Management'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('clipartmanagement/adminhtml_clipart_edit'))
				->_addLeft($this->getLayout()->createBlock('clipartmanagement/adminhtml_clipart_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('clipartmanagement')->__('Clipart does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {		
			
			if(isset($_FILES['clipart_image']['name']) && $_FILES['clipart_image']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('clipart_image');
					$id     = $this->getRequest()->getParam('id');
					$model  = Mage::getModel('clipartmanagement/clipart')->load($id);					
											
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('swf'));
					$uploader->setAllowRenameFiles(false);
					
					$uploader->setFilesDispersion(false);
																			
					$file_parts = explode(".", $_FILES['clipart_image']['name']);
					$file_parts_rev = array_reverse($file_parts);
					$file_extension = $file_parts_rev[0];
				
					$now = date("YmdHis");
					
					$img = "Clipart_".$now.".".$file_extension;	
					$clipart_img = "Clipart_".$now;
					
					$path = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS;
					//$thumbpath = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS .'thumb'. DS;
					$uploader->save($path, $img );	
									
					//Delete old images				
					//$this->resize_image($path.$img, $thumbpath.$img);		
					
					if($model->clipart_image != '' && file_exists($path.$model->clipart_image))
						unlink($path.$model->clipart_image);
					
					/*if($model->clipart_image != '' && file_exists($thumbpath.$model->clipart_image))
						unlink($thumbpath.$model->clipart_image);						*/
						
					//this way the name is saved in DB
	  				$data['clipart_image'] = $img;
					
				} catch (Exception $e) {
		      		$this->_getSession()->addError($e->getMessage());
					$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
					return;
		        }
			}
	  			
			$model = Mage::getModel('clipartmanagement/clipart');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			$model->clipart_name = trim($data['clipart_name']);
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('clipartmanagement')->__('Clipart was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('clipartmanagement')->__('Unable to find clipart to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {		
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
			
				$clipartData = Mage::getModel('clipartmanagement/clipart')->load($this->getRequest()->getParam('id'));
				$swfClipart = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS . $clipartData->getClipartImage();				
				if (file_exists($swfClipart)){
					unlink($swfClipart);
				}								
				$model = Mage::getModel('clipartmanagement/clipart');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Clipart was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $clipartmanagementIds = $clipartmanageIds = $this->getRequest()->getParam('clipartmanagement');
        if(!is_array($clipartmanagementIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select clipart(s)'));
        } else {
            try {
                foreach ($clipartmanagementIds as $clipartmanagementIds) {
					
                    $clipartmanagement = Mage::getModel('clipartmanagement/clipart')->getCollection()->addFieldToFilter('clipart_id ',$clipartmanagementIds)->toOptionArray();
					
					$img = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS . $clipartmanagement[0]['label'];
					$thumbimg = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS .'thumb'. DS . $clipartmanagement[0]['label'];
					unlink($img);
					unlink($thumbimg);
							
					$clipartmanagement = Mage::getModel('clipartmanagement/clipart')->load($clipartmanagementIds);
                    $clipartmanagement->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($clipartmanageIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $clipartmanagementIds = $clipartmanageIds = $this->getRequest()->getParam('clipartmanagement');
        if(!is_array($clipartmanagementIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select clipart(s)'));
        } else {
            try {
                foreach ($clipartmanagementIds as $clipartmanagementIds) {
                    $clipartmanagement = Mage::getSingleton('clipartmanagement/clipart')
                        ->load($clipartmanagementIds)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
					   }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($clipartmanageIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'clipartmanagement.csv';
        $content    = $this->getLayout()->createBlock('clipartmanagement/adminhtml_clipart_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'clipartmanagement.xml';
        $content    = $this->getLayout()->createBlock('clipartmanagement/adminhtml_clipart_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
	
	function resize_image($src_filename, $dest_filename){
		$max_width = 500;
		$max_height = 500;
		$tn_max_width = 80;
		$tn_max_height = 80;
		
		chmod ($src_filename, 0777);	//change permissions on source file
	
		list($width, $height, $type, $attr) = getimagesize($src_filename);
		//echo $type; exit;
		if ($type == ""){
		  //echo "<p>Filename suggest that file is not an image.</p>";
		  unlink($src_filename);
		  return false;
		  exit;
		}
		$ratio = max($width/$max_width, $height/$max_height);	//get ratio to be used to resize image file
		$ratio_tn = max($width/$tn_max_width, $height/$tn_max_height);	//get ratio to be used to resize thumbnail image file
		if ($ratio <= 1){	//don't resize photo, just move it		   
			//create thumbnail -----------------------------------
			$new_width_tn = intval($width / $ratio_tn);	//calculate new width
			$new_height_tn = intval($height / $ratio_tn);
			$dest_tn = imagecreatetruecolor($new_width_tn, $new_height_tn);
			imagefill($dest_tn, 0, 0, 0xFFFFFF);
			$white = imagecolorallocate($dest_tn, 255, 255, 255);
			// Make the background transparent
			imagecolortransparent($dest_tn, $white);
			switch($type){
				case 3:       //PNG					
					$src_tn = imagecreatefrompng($src_filename);
					break;
				case 2:       //JPEG
					$src_tn = imagecreatefromjpeg($src_filename);
					break;
				case 1:    // GIF
					$src_tn = imagecreatefromgif($src_filename);
					break;
				default:
					return FALSE;
					break;
			}
			imagecopyresampled($dest_tn, $src_tn, 0, 0, 0, 0, $new_width_tn, $new_height_tn, $width, $height);
			//create resized thumbnail image file
			$file_tn = $dest_filename;    	
			switch($type){
				case 3:					
					imagepng($dest_tn,$file_tn);					
					break;
				case 2:					
					imagejpeg($dest_tn,$file_tn, 85);					
					break;
				case 1:
					imagegif($dest_tn,$file_tn);					
					break;
			}
			chmod ($file_tn, 0777);
			return 1;
			break;		
		} else {	//create thumbnail & resize photo
			//create thumbnail -----------------------------------
			$new_width_tn = intval($width / $ratio_tn);	//calculate new width
			$new_height_tn = intval($height / $ratio_tn);
			$dest_tn = imagecreatetruecolor($new_width_tn, $new_height_tn);
			imagefill($dest_tn, 0, 0, 0xFFFFFF);
			$white = imagecolorallocate($dest_tn, 255, 255, 255);
			// Make the background transparent
			imagecolortransparent($dest_tn, $white);
			switch($type){
				case 3:       //PNG					
					$src_tn = imagecreatefrompng($src_filename);
					break;
				case 2:       //JPEG
					$src_tn = imagecreatefromjpeg($src_filename);
					break;
				case 1:
					$src_tn = imagecreatefromgif($src_filename);
					break;
				default:
					return FALSE;
					break;
			}
			imagecopyresampled($dest_tn, $src_tn, 0, 0, 0, 0, $new_width_tn, $new_height_tn, $width, $height);
			//create resized thumbnail image file
			$file_tn = $dest_filename;    	
			switch($type){
				case 3:					
					imagepng($dest_tn,$file_tn);					
					break;
				case 2:					
					imagejpeg($dest_tn,$file_tn, 85);		
					chmod ($file_tn, 0777);			
					break;
				case 1:
					imagegif($dest_tn,$file_tn);					
					chmod ($file_tn, 0777);
					break;	
			}			
		}
	}
}?>
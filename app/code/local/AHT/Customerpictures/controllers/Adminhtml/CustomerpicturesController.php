<?php

class AHT_Customerpictures_Adminhtml_CustomerpicturesController extends Mage_Adminhtml_Controller_action
{
	
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('customerpictures/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction();
		$this->_addContent($this->getLayout()->createBlock('customerpictures/adminhtml_customerpictures'));
		$this->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('customerpictures/customerpictures')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('customerpictures_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('customerpictures/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('customerpictures/adminhtml_customerpictures_edit'))
				->_addLeft($this->getLayout()->createBlock('customerpictures/adminhtml_customerpictures_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customerpictures')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('filename');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS ;
					$uploader->save($path, $_FILES['filename']['name'] );
					
				} catch (Exception $e) {
		      
		        }
	        
		        //this way the name is saved in DB
	  			$data['filename'] = $_FILES['filename']['name'];
			}
	  			
	  			
			$model = Mage::getModel('customerpictures/customerpictures');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customerpictures')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customerpictures')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('customerpictures/customerpictures');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $customerpicturesIds = $this->getRequest()->getParam('customerpictures');
        if(!is_array($customerpicturesIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($customerpicturesIds as $customerpicturesId) {
                    $customerpictures = Mage::getModel('customerpictures/customerpictures')->load($customerpicturesId);
                    $customerpictures->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($customerpicturesIds)
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
        $customerpicturesIds = $this->getRequest()->getParam('customerpictures');
        if(!is_array($customerpicturesIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($customerpicturesIds as $customerpicturesId) {
                    $customerpictures = Mage::getSingleton('customerpictures/customerpictures')
                        ->load($customerpicturesId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($customerpicturesIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'customerpictures.csv';
        $content    = $this->getLayout()->createBlock('customerpictures/adminhtml_customerpictures_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'customerpictures.xml';
        $content    = $this->getLayout()->createBlock('customerpictures/adminhtml_customerpictures_grid')
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
	
	public function getImage(){
		$id = $this->getRequest()->getParam('id');
		$image = Mage::getModel('customerpictures/images')->load($id);
		return $image;
	}
	
	public function winnerAction(){
		$image = $this->getImage();
		if($image->getWinnerTime()!='')
			$image->setWinnerTime(False);
		else{
			$image->setWinnerTime(time());
			$image->setUserStatus(0);
			$image->setStatus(2);
		}
		
		$image->save();
	}
	
	public function approveAction(){
		$image = $this->getImage();
		if($image->getStatus()!=2){
			$customer = Mage::getModel('customer/customer')->load($image->getUserId());
			$image->setStatus(2);
			$image->setUpdateTime(time());
			try {
				$image->save();
				$this->sendNotice($customer, 2, $image);
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customerpictures')->__('The picture was successfully approved'));
				
			}catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
		return;
	}
	
	public function denyAction(){
		$image = $this->getImage();
		if($image->getStatus()!=1){
			$customer = Mage::getModel('customer/customer')->load($image->getUserId());
			$image->setStatus(1);
			$image->setWinnerTime(False);
			$image->setUpdateTime(time());
			try {
				$image->save();
				$this->sendNotice($customer, 1, $image);
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customerpictures')->__('The picture was successfully denied'));
			}catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
		return;
	}
	
	public function removeAction(){
		$image = $this->getImage();
		$customer = Mage::getModel('customer/customer')->load($image->getUserId());
		try {
			$imageDir = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$image->getUserId();
			
			$url = $imageDir.DS.$image->getImageName();
			$urlThumb = $baseDir.DS."thumb".DS.$image->getImageName();
			$urlResizeBackend = $imageDir.DS."resize".DS."80x80".DS.$image->getImageName();
			
			$userSize = Mage::getStoreConfig('customerpictures/user/width').'x'.Mage::getStoreConfig('customerpictures/user/height');
			$pageSize = Mage::getStoreConfig('customerpictures/page/width').'x'.Mage::getStoreConfig('customerpictures/page/height');
			$viewSize = Mage::getStoreConfig('customerpictures/view/width').'x'.Mage::getStoreConfig('customerpictures/view/height');
			
			$urlResize1 = $imageDir.DS."resize".DS.$userSize.DS.$image->getImageName();
			$urlResize2 = $imageDir.DS."resize".DS.$pageSize.DS.$image->getImageName();
			$urlResize3 = $imageDir.DS."resize".DS.$viewSize.DS.$image->getImageName();
			
			unlink($url);
			unlink($urlResizeBackend);
			unlink($urlThumb);
			unlink($urlResize1);
			unlink($urlResize2);
			unlink($urlResize3);
			
			$image->delete();
			$this->sendNotice($customer, 3, $image);
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The picture was successfully deleted'));
			$this->_redirect('*/*/index');
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
		}
	}
	
	public function couponAction(){
		$data = $this->getRequest()->getPost('coupon');
		$customerId = Mage::getModel('customerpictures/images')->load($data['id'])->getUserId();
		$customer = Mage::getModel('customer/customer')->load($customerId);
		$customerEmail = $customer->getEmail();
		$code = $data['code'];
		
		try {
			$this->sendCoupon($customer, $code);
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customerpictures')->__('The coupon code was successfully send to %s', $customer->getEmail()));
			$this->_redirect('*/*/index');
		}catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/*/index');
			return;
		}
		
		
	}
	
	public function sendCoupon($customer, $code){
		$templateId = Mage::getStoreConfig('customerpictures/general/email');
		$mailSubject = Mage::getStoreConfig('customerpictures/general/subject');

		$senderName = Mage::getStoreConfig('customerpictures/general/sender');
		$senderEmail = Mage::getStoreConfig('customerpictures/general/sendmail');

		$sender = Array('name'  => $senderName, 'email' => $senderEmail);

		$email = $customer->getEmail();
		$name = $customer->getName();
		
		$vars = Array('customer'=>$customer, 'code' =>$code);

		$storeId = Mage::app()->getStore()->getId(); 

		$translate  = Mage::getSingleton('core/translate');
		
		Mage::getModel('core/email_template')
		->setTemplateSubject($mailSubject)
		->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
		
		$translate->setTranslateInline(true);
	}
	
	public function getStatusLabel($stausId){
		switch ($stausId) {
			case 1:
				return $this->__('Denied');
				break;
			case 2:
				return $this->__('Approved');
				break;
			case 3:
				return $this->__('Removed');
				break;
		}
	}
	
	public function sendNotice($customer, $statusId, $image){
		$templateId = Mage::getStoreConfig('customerpictures/general/notice_email');
		$mailSubject = Mage::getStoreConfig('customerpictures/general/notice_subject');

		$senderName = Mage::getStoreConfig('customerpictures/general/sender');
		$senderEmail = Mage::getStoreConfig('customerpictures/general/sendmail');

		$sender = Array('name'  => $senderName, 'email' => $senderEmail);

		$email = $customer->getEmail();
		$name = $customer->getName();
		
		$status = $this->getStatusLabel($statusId);
		
		$vars = Array('customer'=>$customer, 'status' =>$status, 'picture'=>$image);

		$storeId = Mage::app()->getStore()->getId(); 

		$translate  = Mage::getSingleton('core/translate');
		
		Mage::getModel('core/email_template')
		->setTemplateSubject($mailSubject)
		->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
		
		$translate->setTranslateInline(true);
	}

}
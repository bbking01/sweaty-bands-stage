<?php

class Magestore_Fontmanagement_Adminhtml_AddfontController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() { 
		$this->loadLayout()
			->_setActiveMenu('fontmanagement/addfont')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Font Management'), Mage::helper('adminhtml')->__('Font Management'));
		
		return $this;
	}   
 
	public function indexAction() {		
		$this->_initAction()
			->renderLayout();		
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('fontmanagement/addfont')->load($id);
		
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
						
			Mage::register('fontmanagement_data', $model);
			$this->loadLayout();
			$this->_setActiveMenu('fontmanagement/addfont');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Font Management'), Mage::helper('adminhtml')->__('Font Management'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('fontmanagement/adminhtml_addfont_edit'))
				->_addLeft($this->getLayout()->createBlock('fontmanagement/adminhtml_addfont_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fontmanagement')->__('Font does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$id     = $this->getRequest()->getParam('id');					
			$model  = Mage::getModel('fontmanagement/addfont')->load($id);
			if(isset($_FILES['font_file']['name']) && $_FILES['font_file']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('font_file');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('swf'));
					$uploader->setAllowRenameFiles(false);
					
					$uploader->setFilesDispersion(false);
							
					$now = date("dis");
					$file_parts = explode(".", $_FILES['font_file']['name']);
					$file_parts_rev = array_reverse($file_parts);
					$file_extension = $file_parts_rev[0];
					
					$now = date("YmdHis");
					
					$font_file = "Font_".$now.".".$file_extension;
					//$font_file = "Font_".$now."_".$_FILES['font_file']['name'];	
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS .'font'. DS;
					
						
				
					$uploader->save($path, $font_file );
					
					if($model->font_file != '' && file_exists($path.$model->font_file))
						unlink($path.$model->font_file);
					//this way the name is saved in DB
		  			$data['font_file'] = $font_file;
					
				} catch (Exception $e) {
					 $this->_getSession()->addError($e->getMessage());
					 $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
					 return;
		        }
			}
			
			if(isset($_FILES['font_image']['name']) && $_FILES['font_image']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('font_image');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
										
					$uploader->setFilesDispersion(false);
							
					$file_parts = explode(".", $_FILES['font_image']['name']);
					$file_parts_rev = array_reverse($file_parts);
					$file_extension = $file_parts_rev[0];
				
					$now = date("YmdHis");
					
					$font_img = 'img'."_".$now.".".$file_extension;	
					
					$path = Mage::getBaseDir('media') . DS .'font'. DS .'images'. DS;
					$uploader->save($path, $font_img );					
					
					if($model->font_image != '' && file_exists($path.$model->font_image))
						unlink($path.$model->font_image);
					 //this way the name is saved in DB					 
					$data['font_image'] = $font_img;
				} catch (Exception $e) {
					if(isset($data['font_file']) && file_exists('/media/font/'.$data['font_file']) && !($this->getRequest()->getParam('id')))
					{
						unlink('/media/font/'.$data['font_file']);
					}
		      		$this->_getSession()->addError($e->getMessage());
					 $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
					 return;
		        }
			}
	  			  		
						
			$model = Mage::getModel('fontmanagement/addfont');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			$model->font_name = trim($data['font_name']);
			
			try {
				if($model->getId())
					$font_cat = Mage::getModel('fontmanagement/addfont')->getCollection()->addFieldToFilter('font_name',$model->font_name)->addFieldToFilter('font_id',array('neq' => $model->getId()))->toOptionArray();		
				else				
					$font_cat = Mage::getModel('fontmanagement/addfont')->getCollection()->addFieldToFilter('font_name',$model->font_name)->toOptionArray();		
								
				/*
				Code commented by bhagyashri as per client mail starts
				if(!empty($font_cat))
				{
					throw new Exception('Font Name already exists.');
				}
				Code commented by bhagyashri as per client mail starts
				*/
			}catch (Exception $e) {				
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());                
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
			}
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fontmanagement')->__('Font was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fontmanagement')->__('Unable to find font to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('fontmanagement/addfont');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Font was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $fontmanagementIds = $fontmanageIds = $this->getRequest()->getParam('fontmanagement');
        if(!is_array($fontmanagementIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select font(s)'));
        } else {
            try {
                foreach ($fontmanagementIds as $fontmanagementIds) {
					$fontmanagement = Mage::getModel('fontmanagement/addfont')->getCollection()->addFieldToFilter('font_id',$fontmanagementIds)->toOptionArray();
										
					$img = Mage::getBaseDir('media') . DS .'font'. DS .'images'. DS . $fontmanagement[0]['value'];
					$file = Mage::getBaseDir('media') . DS .'font'. DS . $fontmanagement[0]['label'];
					if(file_exists($img))
						unlink($img);
					if(file_exists($file))
						unlink($file);
					
                    $fontmanagement = Mage::getModel('fontmanagement/addfont')->load($fontmanagementIds);
                    $fontmanagement->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($fontmanageIds)
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
        $fontmanagementIds = $fontmanageIds = $this->getRequest()->getParam('fontmanagement');
        if(!is_array($fontmanagementIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select font(s)'));
        } else {
            try {
                foreach ($fontmanagementIds as $fontmanagementIds) {
                    $fontmanagement = Mage::getSingleton('fontmanagement/addfont')
                        ->load($fontmanagementIds)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($fontmanageIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'fontmanagement.csv';
        $content    = $this->getLayout()->createBlock('fontmanagement/adminhtml_addfont_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'fontmanagement.xml';
        $content    = $this->getLayout()->createBlock('fontmanagement/adminhtml_addfont_grid')
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
}?>
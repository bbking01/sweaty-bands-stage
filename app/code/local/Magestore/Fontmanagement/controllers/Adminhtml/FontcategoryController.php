<?php

class Magestore_Fontmanagement_Adminhtml_FontcategoryController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() { 
		$this->loadLayout()
			->_setActiveMenu('fontmanagement/fontcategory')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Font Category Management'), Mage::helper('adminhtml')->__('Font Category Management'));
		return $this;
	}   
 
	public function indexAction() {		
		$this->_initAction()
			->renderLayout();		
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('fontmanagement/fontcategory')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('fontmanagement_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('fontmanagement/fontcategory');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Font Management'), Mage::helper('adminhtml')->__('Font Management'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('fontmanagement/adminhtml_fontcategory_edit'))
				->_addLeft($this->getLayout()->createBlock('fontmanagement/adminhtml_fontcategory_edit_tabs'));

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
				  			
			$model = Mage::getModel('fontmanagement/fontcategory');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
							
			$model->category_name = trim($data['category_name']);
			
			try {
				if($model->getId())
					$font_cat = Mage::getModel('fontmanagement/fontcategory')->getCollection()->addFieldToFilter('category_name',$model->category_name)->addFieldToFilter('font_cat_id',array('neq' => $model->getId()))->toOptionArray();		
				else				
					$font_cat = Mage::getModel('fontmanagement/fontcategory')->getCollection()->addFieldToFilter('category_name',$model->category_name)->toOptionArray();		
								
				if(!empty($font_cat))
				{
					throw new Exception('Category Name already exists.');
				}
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
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fontmanagement')->__('Font Category was successfully saved'));
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
			
				/*Added code by bhagyashri to delete related fonts also deleted started*/	
					$fontmanagementIds = $this->getRequest()->getParam('id');
                    $fontmanage = Mage::getModel('fontmanagement/addfont')->getCollection()->addFieldToFilter('font_category_id',$fontmanagementIds)->toOptionArray();
					foreach($fontmanage as $fontmanagement)
					{								
										
						$img = Mage::getBaseDir('media') . DS .'font'. DS .'images'. DS . $fontmanagement['value'];
						$file = Mage::getBaseDir('media') . DS .'font'. DS . $fontmanagement['label'];
						if(file_exists($img))
							unlink($img);
						if(file_exists($file))
							unlink($file);
							
						$fontmanagemnt = Mage::getModel('fontmanagement/addfont')->getCollection()->addFieldToFilter('font_image',$fontmanagement['value'])->addFieldToFilter('font_file',$fontmanagement['label'])->toOptionIdArray();	
						
						$fontmanagemnt = Mage::getModel('fontmanagement/addfont')->load($fontmanagemnt[0]['value']);
	                    $fontmanagemnt->delete();
					}
					
				    $fontmanagement = Mage::getModel('fontmanagement/fontcategory')->load($fontmanagementIds);
                    $fontmanagement->delete();
                
				/*Added code by bhagyashri to delete related fonts also deleted ended*/
			
			
				$model = Mage::getModel('fontmanagement/fontcategory');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Font Category was successfully deleted'));
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
                    $fontmanage = Mage::getModel('fontmanagement/addfont')->getCollection()->addFieldToFilter('font_category_id',$fontmanagementIds)->toOptionArray();
										
					foreach($fontmanage as $fontmanagement)
					{								
										
						$img = Mage::getBaseDir('media') . DS .'font'. DS .'images'. DS . $fontmanagement['value'];
						$file = Mage::getBaseDir('media') . DS .'font'. DS . $fontmanagement['label'];
						if(file_exists($img))
							unlink($img);
						if(file_exists($file))
							unlink($file);
							
						$fontmanagemnt = Mage::getModel('fontmanagement/addfont')->getCollection()->addFieldToFilter('font_image',$fontmanagement['value'])->addFieldToFilter('font_file',$fontmanagement['label'])->toOptionIdArray();	
						
						
						$fontmanagemnt = Mage::getModel('fontmanagement/addfont')->load($fontmanagemnt[0]['value']);
						
	                    $fontmanagemnt->delete();
					}
					
				    $fontmanagement = Mage::getModel('fontmanagement/fontcategory')->load($fontmanagementIds);
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
                    $fontmanagement = Mage::getSingleton('fontmanagement/fontcategory')
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
        $content    = $this->getLayout()->createBlock('fontmanagement/adminhtml_fontcategory_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'fontmanagement.xml';
        $content    = $this->getLayout()->createBlock('fontmanagement/adminhtml_fontcategory_grid')
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
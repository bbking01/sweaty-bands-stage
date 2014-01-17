<?php

class Magestore_Printcolormanagement_Adminhtml_PrintcolormanagementController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() { 
		$this->loadLayout()
			->_setActiveMenu('printcolormanagement/printcolormanagement')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Printable Color Management'), Mage::helper('adminhtml')->__('Printable Color Management'));
		return $this;
	}   
 
	public function indexAction() {		
		$this->_initAction()
			->renderLayout();		
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('printcolormanagement/printcolormanagement')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('printcolormanagement_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('printcolormanagement/printcolormanagement');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Printable Color Management'), Mage::helper('adminhtml')->__('Printable Color Management'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('printcolormanagement/adminhtml_printcolormanagement_edit'))
				->_addLeft($this->getLayout()->createBlock('printcolormanagement/adminhtml_printcolormanagement_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('printcolormanagement')->__('Color does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
				  			
			$model = Mage::getModel('printcolormanagement/printcolormanagement');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
							
			$model->color_name = trim($data['color_name']);
			$model->color_code = trim($data['color_code']);
			
			try {
				if($model->getId())
					$exists_color = Mage::getModel('printcolormanagement/printcolormanagement')->getCollection()->addFieldToFilter('color_code',$model->color_code)->addFieldToFilter('color_id',array('neq' => $model->getId()))->toOptionArray();		
				else				
					$exists_color = Mage::getModel('printcolormanagement/printcolormanagement')->getCollection()->addFieldToFilter('color_code',$model->color_code)->toOptionArray();		
								
				if(!empty($exists_color))
				{
					throw new Exception('Color Code already exists.');
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
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('printcolormanagement')->__('Printable Color information successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('printcolormanagement')->__('Unable to find color to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('printcolormanagement/printcolormanagement');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Printable Color was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $printcolormanagementIds = $printcolormanageIds = $this->getRequest()->getParam('printcolormanagement');
        if(!is_array($printcolormanagementIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select color(s)'));
        } else {
            try {
				
                foreach ($printcolormanagementIds as $printcolormanagementIds) {
                  $printcolormanagement = Mage::getModel('printcolormanagement/printcolormanagement')->load($printcolormanagementIds);
                    $printcolormanagement->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($printcolormanageIds)
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
        $printcolormanagementIds = $printcolormanageIds = $this->getRequest()->getParam('printcolormanagement');
        if(!is_array($printcolormanagementIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select font(s)'));
        } else {
            try {
                foreach ($printcolormanagementIds as $printcolormanagementIds) {
                    $printcolormanagement = Mage::getSingleton('printcolormanagement/printcolormanagement')
                        ->load($printcolormanagementIds)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($printcolormanageIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'printcolormanagement.csv';
        $content    = $this->getLayout()->createBlock('printcolormanagement/adminhtml_printcolormanagement_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'printcolormanagement.xml';
        $content    = $this->getLayout()->createBlock('printcolormanagement/adminhtml_printcolormanagement_grid')
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
<?php

class Magestore_Clipartmanagement_Adminhtml_ClipartcategoryController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() { 
		$this->loadLayout()
			->_setActiveMenu('clipartmanagement/clipartcategory')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Clipart Category Management'), Mage::helper('adminhtml')->__('Clipart Category Management'));
		return $this;
	}   
 
	public function indexAction() {		
		$this->_initAction()
			->renderLayout();		
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('clipartmanagement/clipartcategory')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('clipartmanagement_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('clipartmanagement/clipartcategory');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Clipart Management'), Mage::helper('adminhtml')->__('Clipart Management'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('clipartmanagement/adminhtml_clipartcategory_edit'))
				->_addLeft($this->getLayout()->createBlock('clipartmanagement/adminhtml_clipartcategory_edit_tabs'));

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
				  			
			$model = Mage::getModel('clipartmanagement/clipartcategory');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
				
			$model->category_name = trim($data['category_name']);
			
			try {
				if($model->getId())
					$clipart_cat = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->addFieldToFilter('category_name',$model->category_name)->addFieldToFilter('clipart_cat_id',array('neq' => $model->getId()))->toOptionArray();		
				else
					$clipart_cat = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->addFieldToFilter('category_name',$model->category_name)->toOptionArray();		
				
				if(!empty($clipart_cat))
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
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('clipartmanagement')->__('Clipart Category was successfully saved'));
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
				$model = Mage::getModel('clipartmanagement/clipartcategory');
				
				/*Code added by bhagyashri to delete related subcategory clipart started*/
					$clipartmanagementIds = $this->getRequest()->getParam('id');
					$clipartsubcat = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->addFieldToFilter('parent_cat_id',$clipartmanagementIds)->toOptionArray();															
					
					foreach($clipartsubcat as $clipsubcat)
					{	
						$clipart = Mage::getModel('clipartmanagement/clipart')->getCollection()->addFieldToFilter('c_category_id',$clipsubcat)->toOptionArray();										
						foreach($clipart as $clip)
						{						
							$img = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS . $clip['label'];
							$thumbimg = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS .'thumb'. DS . $clip['label'];
							unlink($img);
							unlink($thumbimg);
							$clipartmanage = Mage::getModel('clipartmanagement/clipart')->load($clip['value']);
							$clipartmanage->delete();	
						}
					
						$clipartsubcatmanage = Mage::getModel('clipartmanagement/clipartcategory')->load($clipsubcat['value']);
						$clipartsubcatmanage->delete();	
					}
					
					
					$clipart = Mage::getModel('clipartmanagement/clipart')->getCollection()->addFieldToFilter('c_category_id',$clipartmanagementIds)->toOptionArray();										
					foreach($clipart as $clip)
					{						
						$img = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS . $clip['label'];
						$thumbimg = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS .'thumb'. DS . $clip['label'];
						unlink($img);
						unlink($thumbimg);
						$clipartmanage = Mage::getModel('clipartmanagement/clipart')->load($clip['value']);
						$clipartmanage->delete();	
					}
                /*Code added by bhagyashri to delete related subcategory clipart ended*/
				
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Clipart Category was successfully deleted'));
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
					$clipartsubcat = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->addFieldToFilter('parent_cat_id',$clipartmanagementIds)->toOptionArray();															
					
					foreach($clipartsubcat as $clipsubcat)
					{	
						$clipart = Mage::getModel('clipartmanagement/clipart')->getCollection()->addFieldToFilter('c_category_id',$clipsubcat)->toOptionArray();										
						foreach($clipart as $clip)
						{						
							$img = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS . $clip['label'];
							$thumbimg = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS .'thumb'. DS . $clip['label'];
							unlink($img);
							unlink($thumbimg);
							$clipartmanage = Mage::getModel('clipartmanagement/clipart')->load($clip['value']);
							$clipartmanage->delete();	
						}
					
						$clipartsubcatmanage = Mage::getModel('clipartmanagement/clipartcategory')->load($clipsubcat['value']);
						$clipartsubcatmanage->delete();	
					}
					
					
					$clipart = Mage::getModel('clipartmanagement/clipart')->getCollection()->addFieldToFilter('c_category_id',$clipartmanagementIds)->toOptionArray();										
					foreach($clipart as $clip)
					{						
						$img = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS . $clip['label'];
						$thumbimg = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS .'thumb'. DS . $clip['label'];
						unlink($img);
						unlink($thumbimg);
						$clipartmanage = Mage::getModel('clipartmanagement/clipart')->load($clip['value']);
						$clipartmanage->delete();	
					}
					
                    $clipartmanagement = Mage::getModel('clipartmanagement/clipartcategory')->load($clipartmanagementIds);
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
                    $clipartmanagement = Mage::getSingleton('clipartmanagement/clipartcategory')
                        ->load($clipartmanagementIds)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
					//---------------for making respective changes in related subcategory and clipart with this category----------
					$subcatcollection = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->addFieldToFilter('parent_cat_id',$clipartmanagementIds);
					$subcatid=$subcatcollection->getAllIds();
					if(empty($subcatid)) 
					{
					 $subcatid[0] = $clipartmanagementIds;
					}
						foreach ($subcatid as $subcatid) {
						$subcatmanagement = Mage::getSingleton('clipartmanagement/clipartcategory')
                        ->load($subcatid)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
							$clipartcollection = Mage::getModel('clipartmanagement/clipart')->getCollection()->addFieldToFilter('c_category_id',$subcatid);
							$clipartid=$clipartcollection->getAllIds();
							//var_dump($clipartid);exit;
							if(is_array($clipartid)) 
							{
								try {
									foreach ($clipartid as $clipartid) {
									$clipartmanagement = Mage::getSingleton('clipartmanagement/clipart')
									->load($clipartid)
									->setStatus($this->getRequest()->getParam('status'))
									->setIsMassupdate(true)
									->save();
									}
								  } catch (Exception $e) {
										$this->_getSession()->addError($e->getMessage());
								}
							}
						}	
					//-------------------------------------------------------------------------------------------------
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
        $content    = $this->getLayout()->createBlock('clipartmanagement/adminhtml_clipartcategory_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'clipartmanagement.xml';
        $content    = $this->getLayout()->createBlock('clipartmanagement/adminhtml_clipartcategory_grid')
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
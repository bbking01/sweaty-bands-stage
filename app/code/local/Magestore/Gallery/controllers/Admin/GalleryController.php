<?php

class Magestore_Gallery_Admin_GalleryController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('gallery/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Photo Manager'), Mage::helper('adminhtml')->__('Photo Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('gallery/gallery')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('gallery_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('gallery/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('gallery/admin_gallery_edit'))
				->_addLeft($this->getLayout()->createBlock('gallery/admin_gallery_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gallery')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			if($_FILES['filename']['name'] != '') {
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
					//$path = Mage::getBaseDir('media') . DS . 'gallery' . DS;
					//$uploader->save($path, $_FILES['filename']['name'] );
					$file_parts = explode(".", $_FILES['filename']['name']);
					$file_parts_rev = array_reverse($file_parts);
					$file_extension = $file_parts_rev[0];	
					// We set media as the upload dir
					$now = date("YmdHis");
					
					$img = "gallery_".$now.".".$file_extension;	
					
					$path = Mage::getBaseDir('media') . DS . 'gallery' . DS;
					$uploader->save($path, $img );
					
				} catch (Exception $e) {
		      
		        }
	        
		        //this way the name is saved in DB
	  			//$data['filename'] = 'gallery/'.$_FILES['filename']['name'];
				$data['filename'] = 'gallery/'.$img;
			} else {
				$id = $this->getRequest()->getParam('id');
				
				$template = Mage::getModel('gallery/gallery')->getCollection()->addFieldToFilter('gallery_id',$this->getRequest()->getParam('id'));	
				$newarray = array();
				$newarray  = $template->getData();
				
				if(isset($data['filename']['delete']) && $data['filename']['delete'] == 1) {
				$albumimg = str_replace("gallery/","", $data['filename']['value']);
				
				$img2 = Mage::getBaseDir('media') . DS .'gallery'. DS . $albumimg;
				unlink($img2);	
				$data['filename'] = '';
				
				} else {
					//unset($data['filename']);
					$data['filename'] = $newarray[0]['filename'];
				}
			}
	  			 
	  			
			$model = Mage::getModel('gallery/gallery');		
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

				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('gallery')->__('Ideas was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gallery')->__('Unable to find photo to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('gallery/gallery');
				
				/*Code added by bhagyashri to delete related  template started*/
					$templatemanagementIds = $this->getRequest()->getParam('id');
					$tmpl = Mage::getModel('gallery/gallery')->load($templatemanagementIds);			
					$gallery_id = $tmpl['gallery_id'];
					$galleryimage = $tmpl['filename'];
					$galleryimg = str_replace("gallery/","", $galleryimage);
					
					$img2 = Mage::getBaseDir('media') . DS .'gallery'. DS . $galleryimg;
					
					unlink($img2);
                /*Code added by bhagyashri to delete related template ended*/
				
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ideas was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $galleryIds = $this->getRequest()->getParam('gallery');
        if(!is_array($galleryIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select photo(s)'));
        } else {
            try {
                foreach ($galleryIds as $galleryId) {
                    $gallery = Mage::getModel('gallery/gallery')->load($galleryId);
					/*Code added by bhagyashri to delete related  template started*/
					$templatemanagementIds = $galleryId;
					$tmpl = Mage::getModel('gallery/gallery')->load($templatemanagementIds);			
					$gallery_id = $tmpl['gallery_id'];
					$galleryimage = $tmpl['filename'];
					$galleryimg = str_replace("gallery/","", $galleryimage);
					
					
					$img2 = Mage::getBaseDir('media') . DS .'gallery'. DS . $galleryimg;
				
					unlink($img2);
                	/*Code added by bhagyashri to delete related template ended*/
                    $gallery->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($galleryIds)
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
        $galleryIds = $this->getRequest()->getParam('gallery');
        if(!is_array($galleryIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select photo(s)'));
        } else {
            try {
                foreach ($galleryIds as $galleryId) {
                    $gallery = Mage::getSingleton('gallery/gallery')
                        ->load($galleryId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($galleryIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'gallery.csv';
        $content    = $this->getLayout()->createBlock('gallery/admin_gallery_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'gallery.xml';
        $content    = $this->getLayout()->createBlock('gallery/admin_gallery_grid')
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
    public function saveOrderAction()
    {
    	$items = explode('|',$this->getRequest()->getParam('items'));
    	$model = Mage::getModel('gallery/gallery');
    	foreach($items as $item)
    	{
    		$_item = explode('_',$item);	//$_item[0] => id, $_item[1]=>order
    		$model->load($_item[0]);
    		$model->setOrder($_item[1]);
    		$model->save();
    	}
    	$this->_redirect('*/*/index');
    }
	public function customizeAction()
	{
        $this->getResponse()->setBody(
          $this->getLayout()->createBlock('gallery/admin_gallery_edit_tab_tool')->toHtml()
        );

	}
}

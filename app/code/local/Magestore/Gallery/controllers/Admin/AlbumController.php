<?php

class Magestore_Gallery_Admin_AlbumController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('gallery/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Album Manager'), Mage::helper('adminhtml')->__('Album Manager'));
		
		return $this;
	}  
	public function indexAction() {
		
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('gallery/album')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('album_data', $model);

			$this->loadLayout();
			//$this->getLayout()->getBlock('head')->addJs('gallery/js/gallery.js');
			$this->_setActiveMenu('gallery/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('gallery/admin_album_edit'))
				->_addLeft($this->getLayout()->createBlock('gallery/admin_album_edit_tabs'));

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
				if(isset($data['filename']['delete']) && $data['filename']['delete'] == 1) {
					 
					$albumimg = str_replace("gallery/","", $data['filename']['value']);
					
					$img2 = Mage::getBaseDir('media') . DS .'gallery'. DS . $albumimg;
					unlink($img2);	
					 $data['filename'] = '';
				} else {
					unset($data['filename']);
				}
			}
	  		
			$model = Mage::getModel('gallery/album');
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
				
				// insert new url rewrite
				$suffix = Mage::getStoreConfig('gallery/info/album_suffix');
				$suffix = strlen($suffix)?$suffix:".html";
				if(!strlen($model->getUrlKey()))
				{
					$rq_path = trim(strtolower($model->getTitle())). $suffix;
				}else
				{
					$rq_path = $model->getUrlKey().$suffix;
				}
				while( is_int(strpos($rq_path,'  ')))
				{
					$rq_path = str_replace('  ',' ',$rq_path);
				}
				$rq_path = str_replace(' ','-',$rq_path);

				$url_rewrite_id = $data['url_rewrite_id']?$data['url_rewrite_id']:null;
				$rewriteModel = Mage::getModel('core/url_rewrite');
				$data = array(
					'url_rewrite_id'=> $url_rewrite_id,
					'is_system'		=> 1,
					'id_path'		=> 'gallery/album/'.$model->getId(),
					'request_path'	=> "gallery/".$rq_path,
					'target_path'	=> 'gallery/view/album/id/'.$model->getId(),
				);					
					$rewriteModel->setData($data);
                    $rewriteModel->save();
                    $data = array('album_id'=>$model->getId());
                    $data['url_key'] = str_replace($suffix,'',$rq_path);
                    $data['url_rewrite_id'] = $rewriteModel->getId();

				Mage::getModel('gallery/album')->setData($data)->save();
            					
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('gallery')->__('Category was successfully saved'));
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
				$model = Mage::getModel('gallery/album');
				 
				$model->load($this->getRequest()->getParam('id'));
				
				
				   /*Code added by bhagyashri to delete related category template started*/
					$templatemanagementIds = $this->getRequest()->getParam('id');
					
					$templatecat = Mage::getModel('gallery/album')->getCollection()->addFieldToFilter('album_id',$templatemanagementIds);																
					
						$template = Mage::getModel('gallery/gallery')->getCollection()->addFieldToFilter('album_id',$templatemanagementIds);																
						$newarray = array();
						$newarray  = $templatecat->getData();
						$albumimage = $newarray[0]['filename'];
						$albumimg = str_replace("gallery/","", $albumimage);
						foreach($template->getData() as $tmpl)
						{						
							$gallery_id = $tmpl['gallery_id'];
							$filename = $tmpl['filename'];
							$album_id = $tmpl['album_id'];
							
							$galleryimg = str_replace("gallery/","", $filename);
							$img4 = Mage::getBaseDir('media') . DS .'gallery'. DS . $galleryimg;
							unlink($img4);
							
							$gallerymanage = Mage::getModel('gallery/gallery')->load($gallery_id );
							$gallerymanage->delete();	
						}
                /*Code added by bhagyashri to delete related ategory template ended*/
				//Delete the template category images starts
				$img2 = Mage::getBaseDir('media') . DS .'gallery'. DS . $albumimg;
				unlink($img2);
				//Delete the template category images ends
				$rewriteModel = Mage::getModel('core/url_rewrite')->load($model->getUrlRewriteId())->delete();
				$model->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Category was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $albumIds = $this->getRequest()->getParam('album');
        if(!is_array($albumIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select photo(s)'));
        } else {
            try {
                foreach ($albumIds as $albumId) {
					$model = Mage::getModel('gallery/album')->load($albumId);
					
					
					/*Code added by bhagyashri to delete related category template started*/
					$templatemanagementIds = $albumId;
					$template = Mage::getModel('gallery/gallery')->getCollection()->addFieldToFilter('album_id',$templatemanagementIds);																
						foreach($template->getData() as $tmpl)
						{						
							$gallery_id = $tmpl['gallery_id'];
							$filename = $tmpl['filename'];
							$album_id = $tmpl['album_id'];
							
							$galleryimg = str_replace("gallery/","", $filename);
							//$img3 = Mage::getBaseDir('media') . DS .'gallery'. DS .'cache'.  DS .'85x65'. DS .'c1bcce4ed8183690a609cf7a7c77eeff'.DS . $galleryimg;
							$img4 = Mage::getBaseDir('media') . DS .'gallery'. DS . $galleryimg;
							//unlink($img3);
							unlink($img4);
							
							
							$gallerymanage = Mage::getModel('gallery/gallery')->load($gallery_id );
							$gallerymanage->delete();	
						}
                /*Code added by bhagyashri to delete related ategory template ended*/
				//Delete the template category images starts
					$templatecat = Mage::getModel('gallery/album')->getCollection()->addFieldToFilter('album_id',$albumId);
					$newarray = array();
					$newarray  = $templatecat->getData();
					$albumimage = $newarray[0]['filename'];
					$albumimg = str_replace("gallery/","", $albumimage);
			
				//$img1 = Mage::getBaseDir('media') . DS .'gallery'. DS .'cache'.  DS .'85x65'. DS .'c1bcce4ed8183690a609cf7a7c77eeff'.DS . $albumimg;
				$img2 = Mage::getBaseDir('media') . DS .'gallery'. DS . $albumimg;
				//unlink($img1);
				unlink($img2);
				//Delete the template category images ends
					
					$rewriteModel = Mage::getModel('core/url_rewrite')->load($model->getUrlRewriteId())->delete();
					$model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($albumIds)
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
        $albumIds = $this->getRequest()->getParam('album');
        if(!is_array($albumIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select photo(s)'));
        } else {
            try {
                foreach ($albumIds as $albumId) {
                    $album = Mage::getSingleton('gallery/album')
                        ->load($albumId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($albumIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'album.csv';
        $content    = $this->getLayout()->createBlock('gallery/admin_album_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'album.xml';
        $content    = $this->getLayout()->createBlock('gallery/admin_album_grid')
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
    	$model = Mage::getModel('gallery/album');
    	foreach($items as $item)
    	{
    		$_item = explode('_',$item);	//$_item[0] => id, $_item[1]=>order
    		$model->load($_item[0]);
    		$model->setOrder($_item[1]);
    		$model->save();
    	}
    	$this->_redirect('*/*/index');
    }
}

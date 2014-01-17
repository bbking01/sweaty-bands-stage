<?php

class Magestore_Gallery_Admin_IdeasimportController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('gallery/ideasimport')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Ideas Import Manager'), Mage::helper('adminhtml')->__('Ideas Import Manager'));
		
		return $this;
	}  
	public function indexAction() {
		
		$this->_initAction()
			->renderLayout();
	}

	// Import For Fonts
	public function importPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_fonts_file']['tmp_name'])) {
            try {
                $this->_importRates();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('gallery')->__('The Fonts has been imported.'));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gallery')->__('Invalid file upload attempt'));
            }
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gallery')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/admin_ideasimport');
    }

    protected function _importRates()
    {
        $fileName   = $_FILES['import_fonts_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);		
		
        /** checks columns */
        $csvFields  = array(
            0   => Mage::helper('gallery')->__('Name'),
            1   => Mage::helper('gallery')->__('Category'),
            2   => Mage::helper('gallery')->__('Image'),
			3   => Mage::helper('gallery')->__('Designdata'),
            4   => Mage::helper('gallery')->__('Status'),
        );


        $stores = array();
        $unset = array();
        $storeCollection = Mage::getModel('core/store')->getCollection()->setLoadDefault(false);
		
        for ($i=5; $i<count($csvData[0]); $i++) {
            $header = $csvData[0][$i];
            $found = false;
            foreach ($storeCollection as $store) {
                if ($header == $store->getCode()) {
                    $csvFields[$i] = $store->getCode();
                    $stores[$i] = $store->getId();
                    $found = true;
                }
            }
            if (!$found) {
                $unset[] = $i;
            }

        }

        $regions = array();

        if ($unset) {
            foreach ($unset as $u) {
                unset($csvData[0][$u]);
            }
        }
        if ($csvData[0] == $csvFields) 
		{
			$img_count = 0;
            foreach ($csvData as $k => $v) 
			{
                $img_count++;
				if ($k == 0) {
                    continue;
                }

                //end of file has more then one empty lines
                if (count($v) <= 1 && !strlen($v[0])) {
                    continue;
                }
                if ($unset) {
                    foreach ($unset as $u) {
                        unset($v[$u]);
                    }
                }		
				
				if (count($csvFields) != count($v) || $v[0] == '') {
                    Mage::throwException(Mage::helper('gallery')->__('Invalid file upload attempt.'));
                }
				
				
				// For Image File
				$image_parts = explode(".", $v[2]);
				$image_parts_rev = array_reverse($image_parts);
				$image_extension = $image_parts_rev[0];
				
				$image_path = Mage::getBaseDir('media') . DS .'gallery'. DS . $v[2];
				if(strtolower($image_extension) != 'jpg' && strtolower($image_extension) != 'jpeg' && strtolower($image_extension) != 'gif' && strtolower($image_extension) != 'png')
				{
					Mage::throwException(Mage::helper('gallery')->__('Please, Enter only jpg/jpeg/gif/png images.'));
				}else if (!file_exists($image_path)) {
                    Mage::throwException(Mage::helper('gallery')->__('Image file not found at "media/gallery" folder.'));
                }
				$imageName = $v[2];			
				$now = date("dis");					
				$image_file = "gallery_".$now."_".$v[2];				
				
				$gallery_image_path = Mage::getBaseDir('media') . DS .'gallery'. DS . $image_file;	
				
				copy($image_path, $gallery_image_path);
				$imageFile = "gallery/".$image_file;
				if(!file_exists($gallery_image_path))
				{
					Mage::throwException(Mage::helper('fontmanagement')->__('Image file uploading failed.'));
				}			
				
				
				if ($v[1] != '') {
					$designIdeaData  = array(
						'title' => $v[0],
						'album_id' => $v[1],
						'filename' => $imageFile,
						'designdata' => $v[4],
						'status' => $v[3],
						'created_time'  => now(),
						'update_time'  => now(),
					);			
					
					$designIdeaModel = Mage::getModel('gallery/gallery');			
					foreach($designIdeaData as $dataName => $dataValue) {
						$designIdeaModel->setData($dataName, $dataValue);
					}

					/* $titles = array();
					foreach ($stores as $field=>$id) {
						$titles[$id]=$v[$field];
					}
					$rateModel->setTitle($titles); */
					$designIdeaModel->save();
				}
            }			
        }
        else 
		{
            Mage::throwException(Mage::helper('gallery')->__('Invalid file format upload attempt'));
        }
    }
	
	// Import For Clipart Categories
	public function importCategoryPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_designidea_categories_file']['tmp_name'])) {
            try {
                $this->_importCategoryRates();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('gallery')->__('The Design Idea Categories has been imported.'));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gallery')->__('Invalid file upload attempt'));
            }
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gallery')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/admin_ideasimport');
    }

    protected function _importCategoryRates()
    {
        $fileName   = $_FILES['import_designidea_categories_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
		
        /** checks columns */
        $csvFields  = array(           
            0   => Mage::helper('gallery')->__('Name'),
			1   => Mage::helper('gallery')->__('Image'),
			2   => Mage::helper('gallery')->__('Status'),
        );
	
        $stores = array();
        $unset = array();
        $storeCollection = Mage::getModel('core/store')->getCollection()->setLoadDefault(false);
		
        for ($i=3; $i<count($csvData[0]); $i++) {
            $header = $csvData[0][$i];
            $found = false;
            foreach ($storeCollection as $store) {
                if ($header == $store->getCode()) {
                    $csvFields[$i] = $store->getCode();
                    $stores[$i] = $store->getId();
                    $found = true;
                }
            }
            if (!$found) {
                $unset[] = $i;
            }

        }
		
        $regions = array();

        if ($unset) {
            foreach ($unset as $u) {
                unset($csvData[0][$u]);
            }
        }
				
        if ($csvData[0] == $csvFields) 
		{
			$img_count = 0;
            foreach ($csvData as $k => $v) {
                $img_count++;
				if ($k == 0) {
                    continue;
                }

                //end of file has more then one empty lines
                if (count($v) <= 1 && !strlen($v[0])) {
                    continue;
                }
                if ($unset) {
                    foreach ($unset as $u) {
                        unset($v[$u]);
                    }
                }
			
				if (count($csvFields) != count($v) || $v[0] == '') {
                    Mage::throwException(Mage::helper('gallery')->__('Invalid file upload attempt.'));
                }
				
				// For Image File
				$image_parts = explode(".", $v[1]);
				$image_parts_rev = array_reverse($image_parts);
				$image_extension = $image_parts_rev[0];
				
				$image_path = Mage::getBaseDir('media') . DS .'gallery'. DS . $v[1];
				if(strtolower($image_extension) != 'jpg' && strtolower($image_extension) != 'jpeg' && strtolower($image_extension) != 'gif' && strtolower($image_extension) != 'png')
				{
					Mage::throwException(Mage::helper('gallery')->__('Please, Enter only jpg/jpeg/gif/png images.'));
				}else if (!file_exists($image_path)) {
                    Mage::throwException(Mage::helper('gallery')->__('Image file not found at "media/gallery" folder.'));
                }
				$imageName = $v[1];			
				$now = date("dis");					
				$image_file = "gallery_".$now."_".$v[1];				
				
				$gallery_image_path = Mage::getBaseDir('media') . DS .'gallery'. DS . $image_file;	
				
				copy($image_path, $gallery_image_path);
				
				if(!file_exists($gallery_image_path))
				{
					Mage::throwException(Mage::helper('fontmanagement')->__('Image file uploading failed.'));
				}
				
				if ($v[0] != '') {	
					/* echo $v[2];
					echo "<br />";
					if($v[2]!='1' && $v[2]!='0')
					{
						echo "if".$v[2];
						echo "<br />";
						$status = 1;
					}
					if($v[2]=='0')
					{
						echo "if".$v[2];
						echo "<br />";
						$status = 1;
					}
					echo $status;
					echo "<br />"; */
					$imageFile = "gallery/".$image_file;
					$designIdeaCatData  = array(
						'title'=>$v[0],
						'filename'  => $imageFile,						
						'status'  => $v[2],			 			
						'created_time'  => now(),
						'update_time'  => now(),
					);
					$categoryModel = Mage::getModel('gallery/album');					
					foreach($designIdeaCatData as $dataName => $dataValue) {
						$categoryModel->setData($dataName, $dataValue);
					}
					$categoryModel->save();					
				}				
            }					
        }
        else 
		{
            Mage::throwException(Mage::helper('gallery')->__('Invalid file format upload attempt'));
        }
    }
	
	public function sampleCsvAction()
    {
        $fileName   = $this->getRequest()->getParam('filename'); 
		$filePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/gallery/import/'.$fileName;		
       
        if (! is_file ( $filePath ) || ! is_readable ( $filePath )) {
            throw new Exception ( );
        }
		
		$this->getResponse ()
                    ->setHttpResponseCode ( 200 )
                    ->setHeader ( 'Pragma', 'public', true )
                    ->setHeader ( 'Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true )                    
					->setHeader('Content-type', 'application/force-download')
                    ->setHeader ( 'Content-type', 'application/octet-stream', true  )
                    ->setHeader ( 'Content-Length', filesize($filePath) )
                    ->setHeader ('Content-Disposition', 'inline' . '; filename=' . basename($filePath) );
        $this->getResponse ()->clearBody ();
        $this->getResponse ()->sendHeaders ();
        readfile ( $filePath );
        exit(0);
    }
	
	
	
}?>
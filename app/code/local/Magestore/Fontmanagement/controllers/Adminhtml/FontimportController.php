<?php

class Magestore_Fontmanagement_Adminhtml_FontimportController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() { 
		$this->loadLayout()
			->_setActiveMenu('fontmanagement/fontimport')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Font Management'), Mage::helper('adminhtml')->__('Font Management'));
		
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

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fontmanagement')->__('The Fonts has been imported.'));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fontmanagement')->__('Invalid file upload attempt'));
            }
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fontmanagement')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/adminhtml_fontimport');
    }

    protected function _importRates()
    {
        $fileName   = $_FILES['import_fonts_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
		
		
		/*echo '<pre>';
		print_r($csvData);
		echo '</pre>';
		exit;
		*/
		
		
        /** checks columns */
        $csvFields  = array(
            0   => Mage::helper('fontmanagement')->__('Name'),
            1   => Mage::helper('fontmanagement')->__('Category'),
            2   => Mage::helper('fontmanagement')->__('SwfFile'),
			//3   => Mage::helper('fontmanagement')->__('Image'),
            3   => Mage::helper('fontmanagement')->__('Status'),
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
				
                if (count($csvFields) != count($v) || $v[0] == '' || $v[1] == '' || $v[2] == '' || $v[3] == '' ) {
                    Mage::throwException(Mage::helper('fontmanagement')->__('Invalid file upload attempt.'));
                }
				
				// For SWF File
				$swf_parts = explode(".", $v[2]);
				$swf_parts_rev = array_reverse($swf_parts);
				$swf_extension = $swf_parts_rev[0];
				
				$img_path = Mage::getBaseDir('media') . DS .'font'. DS .'images'. DS . $v[2];
				$swf_path = Mage::getBaseDir('media') . DS .'font'. DS . $v[2];
				if(strtolower($swf_extension) != 'swf')
				{
					Mage::throwException(Mage::helper('fontmanagement')->__('Please, Enter only .swf images for font.'));
				}else if (!file_exists($swf_path)) {
                    Mage::throwException(Mage::helper('fontmanagement')->__('Font SWF file not found at "media/font" folder.'));
                }
							
				$now = date("dis");					
				$swf_font_file = "Font_".$now."_".$v[2];	
							
				$img = Mage::getBaseDir('media') . DS .'font'. DS .'images'. DS . $img_name;	
				$swf_font_file_path = Mage::getBaseDir('media') . DS .'font'. DS . $swf_font_file;	
				
				rename($swf_path, $swf_font_file_path);
				 
				if(!file_exists($swf_font_file_path))
				{
					Mage::throwException(Mage::helper('fontmanagement')->__('Font swf file uploading failed.'));
				}
				
                // For Image File				
				/*$image_parts = explode(".", $v[3]);
				$image_parts_rev = array_reverse($image_parts);
				$image_extension = $image_parts_rev[0];
				
				$image_path = Mage::getBaseDir('media') . DS .'font'. DS .'images'. DS . $v[3];
				if (!file_exists($image_path)) {
                    Mage::throwException(Mage::helper('fontmanagement')->__('Font Image not found at "media/font/images" folder.'));
                }
				
				$now = date("YmdHis");	
				$image_name = "img_".$now.'.'.$image_extension;
				$new_image_path = Mage::getBaseDir('media') . DS .'font'. DS .'images'. DS . $image_name;
				
				rename($image_path, $new_image_path);
				 
				if(!file_exists($new_image_path))
				{
					Mage::throwException(Mage::helper('fontmanagement')->__('Font Image file uploading failed.'));
				}*/
				
                $fontData  = array(
					'font_name' => $v[0],
					'font_category_id' => $v[1],
					'font_file' => $swf_font_file,
					//'font_image' => $image_name,
					'status' => $v[3],
				);

				$rateModel = Mage::getModel('fontmanagement/addfont');				
				foreach($fontData as $dataName => $dataValue) {
					$rateModel->setData($dataName, $dataValue);
				}

				$titles = array();
				foreach ($stores as $field=>$id) {
					$titles[$id]=$v[$field];
				}
				$rateModel->setTitle($titles);
				$rateModel->save();
            }			
        }
        else 
		{
            Mage::throwException(Mage::helper('fontmanagement')->__('Invalid file format upload attempt'));
        }
    }
	
	// Import For Clipart Categories
	public function importCategoryPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_font_categories_file']['tmp_name'])) {
            try {
                $this->_importCategoryRates();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fontmanagement')->__('The Font Categories has been imported.'));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fontmanagement')->__('Invalid file upload attempt'));
            }
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fontmanagement')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/adminhtml_fontimport');
    }

    protected function _importCategoryRates()
    {
        $fileName   = $_FILES['import_font_categories_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
		
        /** checks columns */
        $csvFields  = array(
            0   => Mage::helper('fontmanagement')->__('Name'),
            1   => Mage::helper('fontmanagement')->__('Position'),
			2   => Mage::helper('fontmanagement')->__('Status'),
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
				$fontCategoryData = Mage::getModel('fontmanagement/fontcategory')->load($v[0],'category_name');
				$fontCategory = $fontCategoryData->getData();	
				if(!empty($fontCategory))
				{					
					Mage::throwException(Mage::helper('clipartmanagement')->__('%s Category Name already exists.',$v[0]));
				}	
                if (count($csvFields) != count($v) || $v[0] == '') {
                    //Mage::throwException(Mage::helper('fontmanagement')->__('Invalid file upload attempt.'));
                }				
            }
			
			
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
                    //Mage::throwException(Mage::helper('fontmanagement')->__('Invalid file upload attempt.'));
                }
				if ($v[0] != '' && $v[1] != '') {
					$fontCatData  = array(
						'category_name'=>$v[0],
						'position'=>$v[1],
						'status'  => $v[2],
					);

					$rateModel = Mage::getModel('fontmanagement/fontcategory');
					foreach($fontCatData as $dataName => $dataValue) {
						$rateModel->setData($dataName, $dataValue);
					}

					$titles = array();
					foreach ($stores as $field=>$id) {
						$titles[$id]=$v[$field];
					}
					$rateModel->setTitle($titles);
					$rateModel->save();
				}
            }	
        }
        else 
		{
            Mage::throwException(Mage::helper('fontmanagement')->__('Invalid file format upload attempt'));
        }
    }
	
}?>
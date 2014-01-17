<?php

class Magestore_Clipartmanagement_Adminhtml_ClipartimportController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() { 
		$this->loadLayout()
			->_setActiveMenu('clipartmanagement/clipartimport')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Clipart Management'), Mage::helper('adminhtml')->__('Clipart Management'));
		
		return $this;
	}   
 
	public function indexAction() {		
		$this->_initAction()
			->renderLayout();	
	}

	// Import For Cliparts
	public function importPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_cliparts_file']['tmp_name'])) {
            try {
                $this->_importRates();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('clipartmanagement')->__('The Cliparts has been imported.'));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('clipartmanagement')->__('Invalid file upload attempttest'));
            }
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('clipartmanagement')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/adminhtml_clipartimport');
    }

    protected function _importRates()
    {
        $fileName   = $_FILES['import_cliparts_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
		
		 
	/*	echo '<pre>';
		print_r($csvData);
		echo '</pre>';
		exit;
*/
		
        /** checks columns */
        $csvFields  = array(
            0   => Mage::helper('clipartmanagement')->__('Name'),
            1   => Mage::helper('clipartmanagement')->__('Category'),
            2   => Mage::helper('clipartmanagement')->__('Image'),
            3   => Mage::helper('clipartmanagement')->__('Position'),
            4   => Mage::helper('clipartmanagement')->__('Status'),
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
        if ($csvData[0] == $csvFields) {
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
				
				 
			
                if (count($csvFields) != count($v) || $v[0] == '' || $v[1] == '' || $v[2] == '') {
                    //Mage::throwException(Mage::helper('clipartmanagement')->__('Invalid file upload attempt(One of the data may be blank or null).'));
                }
				if($v[2]!='')
				{
					$img_parts = explode(".", $v[2]);
					$img_parts_rev = array_reverse($img_parts);
					$img_extension = $img_parts_rev[0];
					
					
					$img_path = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS . $v[2];
					if(strtolower($img_extension) != 'swf')
					{
						Mage::throwException(Mage::helper('clipartmanagement')->__('Please, Enter only .swf images for clipart.'));
					}else if (!file_exists($img_path)) {
						Mage::throwException(Mage::helper('clipartmanagement')->__('Clipart Images not found at "media/clipart/images" folder.'));
					}
					
					$now = date("YmdHis");	
					$img_name = "Clipart_".$now.$img_count.".".$img_extension;				
					$img = Mage::getBaseDir('media') . DS .'clipart'. DS .'images'. DS . $img_name;	
					
					rename($img_path, $img);
					 
					if(!file_exists($img))
					{
						Mage::throwException(Mage::helper('clipartmanagement')->__('Clipart image uploading failed.'));
					}
				}
                if ($v[0] != '' && $v[1] != '' && $v[2] != '') {
					$clipartData  = array(
						'clipart_name'=>$v[0],
						'c_category_id' => $v[1],
						'clipart_image' => $img_name,
						'position' => $v[3],
						'status'  => $v[4],
					);

					$rateModel = Mage::getModel('clipartmanagement/clipart');
					foreach($clipartData as $dataName => $dataValue) {
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
        else {
            Mage::throwException(Mage::helper('clipartmanagement')->__('Invalid file format upload attempt'));
        }
		
		
    }
	
	// Import For Clipart Categories
	public function importCategoryPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_clipart_categories_file']['tmp_name'])) {
            try {
                $this->_importCategoryRates();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('clipartmanagement')->__('The Clipart Categories has been imported.'));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('clipartmanagement')->__('Invalid file upload attempt'));
            }
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('clipartmanagement')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/adminhtml_clipartimport');
    }

    protected function _importCategoryRates()
    {
        $fileName   = $_FILES['import_clipart_categories_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
		
        /** checks columns */
        $csvFields  = array(
            0   => Mage::helper('clipartmanagement')->__('Name'),
            1   => Mage::helper('clipartmanagement')->__('Position'),
            2   => Mage::helper('clipartmanagement')->__('Status'),
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
        if ($csvData[0] == $csvFields) {
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
				$clipartCategoryData = Mage::getModel('clipartmanagement/clipartcategory')->load($v[0],'category_name');
				$clipartCategory = $clipartCategoryData->getData();	
				if(!empty($clipartCategory))
				{					
					Mage::throwException(Mage::helper('clipartmanagement')->__('%s Category Name already exists.',$v[0]));
				}				
				
                if (count($csvFields) != count($v) || $v[0] == '') {
                  //  Mage::throwException(Mage::helper('clipartmanagement')->__('Invalid file upload attempt111.'));
				  //Mage::throwException(Mage::helper('printcolormanagement')->__('Invalid file upload attempt(Error on line %s ).',$img_count));
                }
								         
                /* $clipartCatData  = array(
					'category_name'=>$v[0],
					'parent_cat_id' => $v[1],
                    'position' => $v[2],
					'status'  => $v[3],
				);

				$rateModel = Mage::getModel('clipartmanagement/clipartcategory');
				foreach($clipartCatData as $dataName => $dataValue) {
					$rateModel->setData($dataName, $dataValue);
				}

				$titles = array();
				foreach ($stores as $field=>$id) {
					$titles[$id]=$v[$field];
				}
				$rateModel->setTitle($titles);
				$rateModel->save(); */
            }	
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
				if ($v[0] != '') {				         
					$clipartCatData  = array(
						'category_name'=>$v[0],
						'position' => $v[1],
						'status'  => $v[2],
					);

					$rateModel = Mage::getModel('clipartmanagement/clipartcategory');
					foreach($clipartCatData as $dataName => $dataValue) {
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
        else {
            Mage::throwException(Mage::helper('clipartmanagement')->__('Invalid file format upload attempt'));
        }
    }
	
}?>
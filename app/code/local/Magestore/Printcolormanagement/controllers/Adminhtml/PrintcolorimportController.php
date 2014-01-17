<?php

class Magestore_Printcolormanagement_Adminhtml_PrintcolorimportController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() { 
		$this->loadLayout()
			->_setActiveMenu('printcolormanagement/printcolorimport')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Printable Color Management'), Mage::helper('adminhtml')->__('Printable Color Management'));
		
		return $this;
	}   
 
	public function indexAction() {		
		$this->_initAction()
			->renderLayout();		
	}

	// Import For Printable Color
	public function importPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_printcolor_file']['tmp_name'])) {
            try {
                $this->_importPrintcolorRates();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('printcolormanagement')->__('The Printable Color has been imported.'));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('printcolormanagement')->__('Invalid file upload attempt'));
            }
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('printcolormanagement')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/adminhtml_printcolorimport');
    }

    protected function _importPrintcolorRates()
    {
        $fileName   = $_FILES['import_printcolor_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
		
        /** checks columns */
        $csvFields  = array(
            0   => Mage::helper('printcolormanagement')->__('Name'),
            1   => Mage::helper('printcolormanagement')->__('Code'),
            2   => Mage::helper('printcolormanagement')->__('Status'),
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
		
	/*echo '<pre>';
		print_r($csvData[0]);
		exit;
		echo '<pre>';
		print_r($csvFields);
		echo 'diff.=';
		print_r(array_diff($csvData[0],$csvFields));
		exit;*/
		
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
				$colorData = Mage::getModel('printcolormanagement/printcolormanagement')->load($v[1],'color_code');
				$color = $colorData->getData();
				if(!empty($color))
				{
					Mage::throwException(Mage::helper('printcolormanagement')->__('%s Color Code already exists.',$v[1]));
				}				
								
			    // if (count($csvFields) != count($v) || $v[0] == '' || $v[1] == '' || $v[2] == '') {
                    // Mage::throwException(Mage::helper('printcolormanagement')->__('Invalid file upload attempt(Error on line %s ).',$img_count));
                // }			
				
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
			
				if ($v[0] != '' && $v[1] != '' && $v[2] != '') {
                $printcolorData  = array(
					'color_name'=>$v[0],
					'color_code' => $v[1],
					'status'  => $v[2],
				);

				$rateModel = Mage::getModel('printcolormanagement/printcolormanagement');
				foreach($printcolorData as $dataName => $dataValue) {
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
            Mage::throwException(Mage::helper('printcolormanagement')->__('Invalid file format upload attempt'));
        }		
		//exit;
    }
	
}?>
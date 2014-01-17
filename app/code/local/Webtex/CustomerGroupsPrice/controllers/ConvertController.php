<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Groups Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @author     Webtex Dev Team <dev@webtex.com>
 */

class Webtex_CustomerGroupsPrice_ConvertController extends Mage_Adminhtml_Controller_Action
{
    public function exportAction()
    {
        $this->_title($this->__('Customer Groups Price Export'));

        $this->loadLayout();
        $this->_setActiveMenu('system/convert/customer_groups_price/export');
        $this->_addContent($this->getLayout()->createBlock('customergroupsprice/adminhtml_system_convert_export'));
        $this->renderLayout();
    }

	public function importAction()
    {
        $this->_title($this->__('Customer Groups Price Import'));

        $this->loadLayout();
        $this->_setActiveMenu('system/convert/customer_groups_price/import');
        $this->_addContent($this->getLayout()->createBlock('customergroupsprice/adminhtml_system_convert_import'));
        $this->renderLayout();
    }

	public function saveExportAction()
	{
		$request = $this->getRequest();

		$exportConfig = Mage::getModel('customergroupsprice/convert')->loadByAction('export');
		
		$path		= $request->getParam('file_path', false);
		$paths		= $request->getParam('file_path_s', false);
		$delimiter	= $request->getParam('delimiter', false);
		$enclosure	= $request->getParam('enclosure', false);

		$exportConfig->setFilePath($path.','.$paths)
					->setDelimiter($delimiter)
					->setEnclosure($enclosure);

		try {
			$this->_getCsvFile($path, $delimiter, $enclosure,0);
			$this->_getCsvFile($paths, $delimiter, $enclosure,1);
            $exportConfig->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customergroupsprice')->__('Prices where succesfully exported to %s', $path));
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customergroupsprice')->__('An error occurred while exporting prices.'));
        }
        $this->getResponse()->setRedirect($this->getUrl("*/*/export"));
		
	}

	public function saveImportAction()
	{
		$request = $this->getRequest();

		$importConfig = Mage::getModel('customergroupsprice/convert')->loadByAction('import');

		$path		= '';
		$delimiter	= $request->getParam('delimiter', false);
		$enclosure	= $request->getParam('enclosure', false);

		$importConfig->setFilePath($path)
					->setDelimiter($delimiter)
					->setEnclosure($enclosure);

		try {
			$file = $_FILES['file']['name'];
			$path = Mage::getBaseDir('var').DS.'import'.DS;
			$uploader = new Varien_File_Uploader('file');
			$uploader->setAllowRenameFiles(false);
			$uploader->setFilesDispersion(false);
			$uploader->save($path, $file);

			$io = new Varien_Io_File();
			$io->open(array('path' => $path));
			$io->streamOpen($path.$file, 'r');
			$io->streamLock(true);

			$map = $io->streamReadCsv($delimiter, $enclosure);
			$prodModel = Mage::getSingleton('catalog/product');
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$db->query('set foreign_key_checks = 0');
			
			while($data = $io->streamReadCsv($delimiter, $enclosure)){
			    if(!isset($data[3])){
			        $data[3] = 0;
			    }
			    if($data[1]){
			        if($id = $prodModel->getIdBySku($data[1])) {
			            $group = Mage::getModel('customer/group')->getCollection()->addFieldToFilter('customer_group_code',$data[0])->getData();
			            if($group) {
			                $price = Mage::getModel('customergroupsprice/prices')->loadByGroup($id,$group[0]['customer_group_id'],$data[3]);
			                $price->setProductId($id);
			                $price->setGroupId($group[0]['customer_group_id']);
			                $price->setPrice($data[2]);
			                $price->setWebsiteId($data[3]);
			                $price->save();
			            }
			        }
			    } else {
			        continue;
			    }
			}

			$file = $_FILES['file_s']['name'];
			if($file){
			    $path = Mage::getBaseDir('var').DS.'import'.DS;
			    $uploader = new Varien_File_Uploader('file_s');
			    $uploader->setAllowRenameFiles(false);
			    $uploader->setFilesDispersion(false);
			    $uploader->save($path, $file);
			    $io = new Varien_Io_File();
			    $io->open(array('path' => $path));
			    $io->streamOpen($path.$file, 'r');
			    $io->streamLock(true);
			    $map = $io->streamReadCsv($delimiter, $enclosure);
			    $prodModel = Mage::getSingleton('catalog/product');
			    //$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			    //$db->query('set foreign_key_checks = 0');
			
			    while($data = $io->streamReadCsv($delimiter, $enclosure)){
			    if(!isset($data[3])){
			        $data[3] = 0;
			    }
			      if($data[1]){
			        if($id = $prodModel->getIdBySku($data[1])) {
			            $group = Mage::getModel('customer/group')->getCollection()->addFieldToFilter('customer_group_code',$data[0])->getData();
			            if($group) {
			                $price = Mage::getModel('customergroupsprice/specialprices')->loadByGroup($id,$group[0]['customer_group_id'],$data[3]);
			                $price->setProductId($id);
			                $price->setGroupId($group[0]['customer_group_id']);
			                $price->setPrice($data[2]);
			                $price->setWebsiteId($data[3]);
			                $price->save();
			            }
			        }
			      } else {
			        continue;
			      }
			    }
                        }
            $importConfig->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customergroupsprice')->__('Prices where succesfully imported '));
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customergroupsprice')->__($e->getMessage().'An error occurred while importing prices.'));
        }
        $this->getResponse()->setRedirect($this->getUrl("*/*/import"));

	}

    protected function _getCsvFile($file, $delimiter, $enclosure,$type=0)
	{
		$io = new Varien_Io_File();
		$fullPath = Mage::getBaseDir() . $file;
		$parts = pathinfo($fullPath);
		if(!isset($parts['extension']) || strtolower($parts['extension']) != 'csv'){
			Mage::throwException('Error in file extension. Only *.csv files are supported');
		}

		$io->open(array('path' => $parts['dirname']));
                $io->streamOpen($fullPath, 'w+');
                $io->streamLock(true);

		$header = array('group'   => 'Group Name/ID',
		                'sku'     => 'SKU',
		                'price'   => 'Specific Price',
		                'website' => 'Website ID',
		                );
		$io->streamWriteCsv($header, $delimiter, $enclosure);

		if($type==0){
		    $prices = Mage::getModel('customergroupsprice/prices')->getCollection()->addOrder('group_id','ASC');
		} else {
		    $prices = Mage::getModel('customergroupsprice/specialprices')->getCollection()->addOrder('group_id','ASC');
		}

                $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

                $prices->getSelect()->joinLeft(array('product' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
                                               'main_table.product_id = product.entity_id',
                                               array('product.sku'));
                $prices->getSelect()->joinLeft(array('group' => Mage::getSingleton('core/resource')->getTableName('customer_group')),
                                               'main_table.group_id = group.customer_group_id',
                                               array('group.customer_group_code'));
                $content = array();
		foreach($prices as $price){
		        $content['group']   = $price['customer_group_code'];
		        $content['sku']     = $price['sku'];
		        $content['price']   = $price['price'];
		        $content['website'] = $price['website_id'];
			$io->streamWriteCsv($content, $delimiter, $enclosure);
		}
		
		$io->streamUnlock();
               $io->streamClose();
	}
}

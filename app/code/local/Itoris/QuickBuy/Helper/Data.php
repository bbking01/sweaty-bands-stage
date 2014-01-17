<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_QUICKBUY
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

class Itoris_QuickBuy_Helper_Data extends Mage_Core_Helper_Abstract {

	protected $alias = 'quickbuy';
	/** @var null|Itoris_QuickBuy_Model_Settings */
	protected $settings = null;

	public function isAdminRegistered() {
		try {
			return Itoris_Installer_Client::isAdminRegistered($this->getAlias());
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			return false;
		}
	}

	public function isRegisteredAutonomous($website = null) {
		return Itoris_Installer_Client::isRegisteredAutonomous($this->getAlias(), $website);
	}

	public function registerCurrentStoreHost($sn) {
		return Itoris_Installer_Client::registerCurrentStoreHost($this->getAlias(), $sn);
	}

	public function isRegistered($website) {
		return Itoris_Installer_Client::isRegistered($this->getAlias(), $website);
	}

	public function getAlias() {
		return $this->alias;
	}

	public function getSettings() {
		if (is_null($this->settings)) {
			$settingsModel = Mage::getModel('itoris_quickbuy/settings');
			$websiteId = Mage::app()->getWebsite()->getId();
			$storeId = Mage::app()->getStore()->getId();
			$settingsModel->load($websiteId, $storeId);
			$this->settings = $settingsModel;
		}

		return $this->settings;
	}

	/**
	 * @param $keyword
	 * @return Mage_CatalogSearch_Model_Query|null
	 */
	public function getQuery($keyword) {
		/** @var $queryHelper Itoris_QuickBuy_Helper_Query */
		$queryHelper = Mage::helper('itoris_quickbuy/query');
		/* @var $query Mage_CatalogSearch_Model_Query */
		$query = $queryHelper->getQuery();
		$query->setStoreId(Mage::app()->getStore()->getId());
		if ($query->getQueryText() != '') {
			if ($queryHelper->isMinQueryLength()) {
				$query->setId(0)
					->setIsActive(1)
					->setIsProcessed(1);
			} else {
				if ($query->getId()) {
					$query->setPopularity($query->getPopularity()+1);
				} else {
					$query->setPopularity(1);
				}
			}
		} else {
			return null;
		}
		return $query;
	}

	public function getParentGroupedProductSkus($childId) {
		$resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('read');
		$productTable = $resource->getTableName('catalog_product_entity');
		$productLinkTable = $resource->getTableName('catalog_product_link');
		$childId = intval($childId);
		$result = $connection->fetchAll("
			select distinct pr.sku from {$productLinkTable} as rel
				inner join {$productTable} as pr
					on pr.entity_id = rel.product_id
				where rel.linked_product_id = {$childId} and rel.link_type_id = 3
		");
		$skus = array();
		foreach ($result as $row) {
			$skus[] = $row['sku'];
		}
		return $skus;
	}
/*================================================================================================*/	
	//DCS: TODO
	public function getCustomerGroup($customer){
/*================================================================================================*/		
		
	$cID=$customer->getId();
//Mage::log(__LINE__." getCustomerGroup ".$cID);
	
    $customerData = Mage::getModel('customer/customer')->load($cID)->getData();
    $groupID=$customerData ['group_id'];   
     $groupData = Mage::getModel('customer/group')->load($groupID)->getData();
     $groupName = $groupData ['customer_group_code'];
     
		
		return $groupName;
		
	}//DCS: END_getCustomerGroup
	
/*================================================================================================*/	
	//DCS: 
	public function getGroupPrice($sku= '001-10-0144-01'){
/*================================================================================================*/		

//Mage::log(__LINE__." getGroupPrice ".$sku);
//get and load magento base path
$magentoRoot=Mage::getBaseDir();
$pathToHelper=$magentoRoot.'/app/code/local/Webtex/CustomerGroupsPrice/Helper/';
require_once $pathToHelper.'Data.php';

//Don't need these now...
//$CustomerGroupsPrice=new Webtex_CustomerGroupsPrice_Helper_Data();
//$getCustgroups=$CustomerGroupsPrice->getCustomerGroups();
// Mage::log(__LINE__." getCustgroups ".print_r($getCustgroups,1));

$pathToModel=$magentoRoot.'/app/code/local/Webtex/CustomerGroupsPrice/Model/Prices.php';

require_once $pathToModel;
$CustomPrice=new Webtex_CustomerGroupsPrice_Model_Prices();
 
$catalog=  Mage::getSingleton('catalog/product')->getCollection() 
//$catalog= Mage::getModel('catalog/product')->getCollection()             
           // ->addAttributeToSelect('*')
          //  ->addAttributeToSelect('price'); 
             ->addAttributeToSelect('sku'); 

// retrieve product id using sku
foreach($catalog as $prod) {

$stuff=$prod->getData();
if( $stuff['sku']  == $sku){

 $pricing = $CustomPrice->getProductPrice($prod);

  Mage::log(__LINE__." getGroupPrice $sku: ".$pricing); 
return $pricing;   
   
}
}
	
}//END_getGroupPrice	
	

}
 
?>

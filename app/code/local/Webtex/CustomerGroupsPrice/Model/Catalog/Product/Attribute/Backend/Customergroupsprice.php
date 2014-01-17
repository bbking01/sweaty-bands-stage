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

class Webtex_CustomerGroupsPrice_Model_Catalog_Product_Attribute_Backend_Customergroupsprice extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract {

    public function afterLoad($object) {
        $pricesModel = Mage::getModel('customergroupsprice/prices');
        if(!$object->getId()){
            return $this;
        }
        $websiteId = Mage::app()->getStore(Mage::app()->getRequest()->getParam('store'))->getWebsiteId();
        $prices = $pricesModel->getPricesCollection($object->getId(),$websiteId);
        $data = array();
        foreach($prices as $price) {
            $data[$price->getGroupId()] = $price->getPrice();
        }
        $object->setData($this->getAttribute()->getName(), $data);
    }

    public function afterSave($object) {
        $websiteId = Mage::app()->getStore(Mage::app()->getRequest()->getParam('store'))->getWebsiteId();
        $attributeName = $this->getAttribute()->getName();
        $groupPrices = $object->getData($attributeName);

	if ($object->getId()) {
	        Mage::getModel('customergroupsprice/prices')->deleteByProduct($object->getId(),$websiteId);
		Mage::getModel('customergroupsprice/specialprices')->deleteByProduct($object->getId(),$websiteId);
        }
        if($object->getTypeId()!='bundle' && $groupPrices ) {
        foreach ($groupPrices as $groupId => $price) {
            if ($groupId == 0 || $price == '' || $groupId == 'special') {
                continue;
            }
			
            $pricesModel = Mage::getModel('customergroupsprice/prices');
            $pricesModel->setGroupId($groupId);
            $pricesModel->setProductId($object->getId());
            $pricesModel->setPrice($price);
            $pricesModel->setWebsiteId($websiteId);
            $pricesModel->save();
            unset($pricesModel);
        }
        }

		if(isset($groupPrices['special'])){
			foreach ($groupPrices['special'] as $groupId => $price) {
				if ($groupId == 0 || $price == '') {
					continue;
				}
				$pricesSpecModel = Mage::getModel('customergroupsprice/specialprices');
				$pricesSpecModel->setGroupId($groupId);
				$pricesSpecModel->setProductId($object->getId());
				$pricesSpecModel->setPrice($price);
                                $pricesSpecModel->setWebsiteId($websiteId);
				$pricesSpecModel->save();
				unset($pricesSpecModel);
			}
		}

        $object->setData($attributeName, false);
        return $this;
    }

}
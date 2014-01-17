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

class Webtex_CustomerGroupsPrice_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config
{
    protected function _prepareLayout()
    {
    	parent::_prepareLayout();
        if ($this->helper('customergroupsprice')->isEnabled()) {
	    $this->setTemplate('customergroupsprice/super_config.phtml');
        }
    }

    public function getAttributesJson()
    {
	$attributes = Mage::helper('core')->jsonDecode(parent::getAttributesJson());
	$websiteId = Mage::app()->getStore(Mage::app()->getRequest()->getParam('store'))->getWebsiteId();
	if($websiteId == 0){
	    $websiteId = 1;
	}
        foreach($attributes as $id => $attr){
		foreach($attr['values'] as $k => $value){
			foreach(Mage::helper('customergroupsprice')->getCustomerGroups() as $groupId => $groupName){
				$price = Mage::getModel('customergroupsprice/attributes')->loadByData($attr['id'], $value['value_index'], $groupId, $websiteId);
				if($price && $price->getId()){
					$attributes[$id]['values'][$k]['pricing_value_group_'.$groupId] = $price->getPrice();
				} else {
				    $price = Mage::getModel('customergroupsprice/attributes')->loadByData($attr['id'], $value['value_index'], $groupId, 0);
				    if($price && $price->getId()){
					 $attributes[$id]['values'][$k]['pricing_value_group_'.$groupId] = $price->getPrice();
				    }
				}
			}
		}
	}
        return Mage::helper('core')->jsonEncode($attributes);
    }
}
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

class Webtex_CustomerGroupsPrice_Block_Adminhtml_Renderer_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Price
{
    public function render(Varien_Object $row)
    {
        if ($row->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            $row->setPrice($row->getPrice());
        }
        $customer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();
        $websiteId = Mage::app()->getStore(Mage::app()->getRequest()->getParam('store'))->getWebsiteId();
        $price = Mage::getModel('customergroupsprice/prices')->loadByGroup($row->getEntityId(), $customer->getgroupId(),$websiteId);
        if($price->getPrice()){
            $row->setPrice($price->getPrice());
        } else {
            $price = Mage::getModel('customergroupsprice/prices')->loadByGroup($row->getEntityId(), $customer->getgroupId(),0);
            if($price->getPrice()){
                $row->setPrice($price->getPrice());
            }
        }
        return parent::render($row);
    }
}
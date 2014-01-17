<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Block_Adminhtml_Widget_Grid_Column_Renderer_Trxtype extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $value = $this->_getValue($row);
        switch ($value) {
            case AW_Affiliate_Model_Source_Transaction_Profit_Type::ADMIN :
                $html = $this->__('Added by admin');
                break;
            case AW_Affiliate_Model_Source_Transaction_Profit_Type::CUSTOMER_VISIT :
            case AW_Affiliate_Model_Source_Transaction_Profit_Type::CUSTOMER_PURCHASE :
                $html = $this->__getLinkToReferral($row);
                break;
            default:
                $html = $value;
        }
        return $html;
    }

    protected function _getCustomerFromOrder($order)
    {
        if ($order->getCustomerIsGuest()) {
            $customerId = null;
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $customerId = $order->getCustomerId();
            $customerName = $order->getCustomerName();
        }
        return array($customerId, $customerName);
    }

    private function __getLinkToReferral($row)
    {
        $customerId = $customerName = false;
        switch ($row->getData('linked_entity_type')) {
            case AW_Affiliate_Model_Source_Transaction_Profit_Linked::ORDER_ITEM:
                $linkedEntity = $row->getLinkedEntity();
                if ($linkedEntity instanceof Mage_Sales_Model_Order_Item) {
                    list($customerId, $customerName) = $this->_getCustomerFromOrder($linkedEntity->getOrder());
                }
                break;
            case AW_Affiliate_Model_Source_Transaction_Profit_Linked::INVOICE_ITEM:
                /** @var $order Mage_Sales_Model_Order */
                $order = Mage::getModel('sales/order')->loadByIncrementId($row->getData('linked_entity_id'));
                list($customerId, $customerName) = $this->_getCustomerFromOrder($order);
                break;
        }
        if ($customerId) {
            $_href = Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('id' => $customerId));
            return "<a href='" . $_href . "' target='_blank'>" . $customerName . "</a>";
        } elseif ($customerName) {
            return $customerName;
        } else {
            return $this->__('N/A');
        }
        return '';
    }
}

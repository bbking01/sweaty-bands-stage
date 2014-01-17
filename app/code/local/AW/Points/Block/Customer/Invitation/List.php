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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Customer_Invitation_List extends Mage_Core_Block_Template {

    /**
     * Prepares block layout
     * @return
     */
    protected function _prepareLayout() {
        return parent::_prepareLayout();
    }

    /**
     * Returns current customer
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer() {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Returns collection of invited that are assigned to current customer
     * @return AW_Points_Model_Mysql4_Invitation_Collection
     */
    public function getCollection() {
        if (!$this->getData('collection')) {
            $this->setCollection(
                    Mage::getModel('points/invitation')->getCollection()->addCustomerFilter($this->getCustomer())
            );
            $this->updateNumeralStatuses();
        }
        return $this->getData('collection');
    }

    /**
     * Returns toolbar with pages and so on
     * @return Mage_Page_Block_Html_Pager
     */
    public function getToolbarHtml() {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Check if customer has invitation list
     *
     * @return bool
     */
    public function hasInvitees() {
        return $this->getCollection()->count() > 0;
    }

    /**
     * Get back url in account dashboard
     *     
     * @return string
     */
    public function getBackUrl() {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/');
    }

    /**
     * Get Invitation Form Url
     *
     * @return string
     */
    public function getInvitationFormUrl() {
        return $this->getUrl('points/invitation/sendinvitation/');
    }

    /**
     * Change status codes in human readable format
     *
     * @return void
     */
    private function updateNumeralStatuses() {

        $data = $this->getData('collection');

        foreach ($data as $item) {

            switch ($item->getStatus()) {
                case AW_Points_Model_Invitation::INVITATION_NEW:
                    $item->setStatus($this->__('Email wasn\'t sent. Please try later. '));
                    break;
                case AW_Points_Model_Invitation::INVITATION_SENT:
                    $item->setStatus($this->__('Invitation sent'));
                    break;
                case AW_Points_Model_Invitation::INVITATION_ACCEPTED:
                    $item->setStatus($this->__('Invitation accepted'));
                    break;
                case AW_Points_Model_Invitation::INVITEE_IS_CUSTOMER:
                    $item->setStatus($this->__('Invitee became a customer'));
                    break;
                default:
                    break;
            }
        }
    }

    protected function _toHtml() {

        $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_14 . "/";

        if (Mage::helper('points')->magentoLess14())
            $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_13;

        $this->setTemplate("aw_points/customer/" . $magentoVersionTag . "/invitation/list.phtml");

        $html = parent::_toHtml();
        return $html;
    }

}

?>

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


class AW_Points_Block_Customer_Reward_History extends Mage_Core_Block_Template {

    protected function _construct() {
        parent::_construct();

        $collection = Mage::getModel('points/api')
                        ->getCustomerTransactions(
                                Mage::getSingleton('customer/session')
                                ->getCustomer()
                        )->setOrder('change_date', 'DESC');

        $this->setData('collection', $collection);
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'points.rewards.history.pager')
                ->setCollection($this->getCollection());
        $this->setChild('points_pager', $pager);
        return $this;
    }

    public function getPagerHtml() {
        return $this->getChildHtml('points_pager');
    }

    public function getTransactions() {
        return $this->getCollection();
    }

    public function isEnabled() {
        return Mage::helper('points/config')->getIsEnabledRewardsHistory();
    }

    protected function _toHtml() {

        $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_14;

        if (Mage::helper('points')->magentoLess14())
            $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_13;

        $this->setTemplate("aw_points/customer/" . $magentoVersionTag . "/reward/history.phtml");

        $html = parent::_toHtml();
        return $html;
    }

}

?>

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


class AW_Points_Block_Adminhtml_Customer_Edit_Tabs_RewardPoints_Balance extends Mage_Adminhtml_Block_Template {

    protected function _construct() {
        parent::_construct();
        $this->setData('customer', Mage::registry('current_customer'));
        $this->setData('website', $this->getCustomer()->getStore()->getWebsite());
        $this->addData(
                Mage::getModel('points/summary')
                        ->loadByCustomer($this->getCustomer())->getData());
    }

    public function getCurrentBalanceInPoints() {
        return (int) $this->getPoints();
    }

    public function getBalanceLimit() {

        $limit = Mage::helper('points/config')
                ->getMaximumPointsPerCustomer();

        return (int) $limit;
    }

    public function getCurrentBalanceInCurrency() {
        $result = 0;

        try {
            $result = Mage::getModel('points/api')
                    ->changePointsToMoney(
                    $this->getPoints(), $this->getCustomer(), $this->getWebsite()
            );

            if ($result > 0)
                $result = Mage::helper('core')->formatPrice($result);
        } catch (Exception $e) {

            Mage::helper('awcore/logger')->log($this, Mage::helper('points')->__('Unable to convert points into currency to show up in summary section of user account page.'), AW_Core_Model_Logger::LOG_SEVERITY_WARNING, $e->getMessage(), $e->getLine());
        }

        return $result;
    }

}

?>

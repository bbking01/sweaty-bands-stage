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


class AW_Affiliate_Model_Config_Data_Autocreate extends Mage_Core_Model_Config_Data
{
    protected function _afterSave()
    {
        $res = parent::_afterSave();
        if ($this->isValueChanged()) {
            $customerCollection = Mage::getModel('customer/customer')->getCollection();
            $customerCollection->getSelect()->where("FIND_IN_SET(`e`.`group_id`, ?)", $this->getValue());
            $websiteCode = $this->getWebsiteCode();
            if ($websiteCode) {
                $websiteId = Mage::app()->getWebsite($websiteCode)->getId();
                $customerCollection->addFieldtoFilter('website_id', array('eq' => $websiteId));
            }

            $affiliateTableName = Mage::getResourceModel('awaffiliate/affiliate_collection')->getMainTable();
            $customerCollection->getSelect()->joinLeft(
                array('affiliate_table' => $affiliateTableName),
                'affiliate_table.customer_id = e.entity_id',
                array(
                    'affiliate_id' => 'id'
                )
            );
            $customerCollection->getSelect()->where('`affiliate_table`.`id` IS NULL');
            foreach ($customerCollection as $customer) {
                $affiliate = Mage::getModel('awaffiliate/affiliate');
                $affiliate->setData(array(
                    'customer_id' => $customer->getId(),
                    'status' => AW_Affiliate_Model_Source_Affiliate_Status::ACTIVE
                ));
                try {
                    $affiliate->save();
                }
                catch (Exception $e) {
                    //TODO: log
                }
            }
        }
        return $res;
    }
}

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


class AW_Affiliate_Model_Source_Campaign extends AW_Affiliate_Model_Source_Abstract
{
    protected $_collection = null;
    protected $_customer = null;

    public function toOptionArray()
    {
        if (is_null($this->_collection)) {
            $this->_collection = Mage::getModel('awaffiliate/campaign')->getCollection();
        }
        if (($affiliate = Mage::registry('current_affiliate')) && ($affiliate->getCustomer())) {
            $this->_customer = $affiliate->getCustomer();
        }
        if (is_null($this->_customer)) {
            $this->_customer = Mage::helper('customer')->getCurrentCustomer();
        }
        $options = array();
        $helper = $this->_getHelper();
        foreach ($this->_collection as $campaign) {
            if (in_array($this->_customer->getGroupId(), $campaign->getAllowedGroups()) &&
                in_array($this->_customer->getWebsiteId(), $campaign->getStoreIds())
            ) {
                $options[] = array('value' => $campaign->getId(), 'label' => $helper->__($campaign->getName()));
            }
        }
        return $options;
    }
}

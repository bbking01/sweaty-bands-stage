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


class AW_Affiliate_Block_Customer_Topinfo extends Mage_Core_Block_Template
{
    /**
     * @return AW_Affiliate_Model_Campaign
     */
    public function getCurrentCampaign()
    {
        return Mage::registry('current_campaign');
    }

    public function getCurrentCampaignDescription()
    {
        $currentCampaign = $this->getCurrentCampaign();
        if ($currentCampaign) {
            $processor = Mage::helper('cms')->getBlockTemplateProcessor();
            return $processor->filter($currentCampaign->getData('description'));
        }
    }
}

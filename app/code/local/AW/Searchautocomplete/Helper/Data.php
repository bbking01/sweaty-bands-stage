<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Searchautocomplete
 * @version    3.2.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AW_Searchautocomplete_Helper_Data extends Mage_Core_Helper_Abstract
{

    private function isAdvancedSearchInstalled()
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        return array_key_exists('AW_Advancedsearch', $modules)
            && 'true' == (string)$modules['AW_Advancedsearch']->active;
    }

    public function canUseADVSearch()
    {
        if(!$this->isAdvancedSearchInstalled()) return false;
        return (bool) Mage::getStoreConfig('searchautocomplete/interface/advsearch_integration') && Mage::helper('awadvancedsearch')->isEnabled();

    }
}

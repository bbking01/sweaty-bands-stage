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

class AW_Affiliate_Model_Source_Profit_Type extends AW_Affiliate_Model_Source_Abstract
{
    const FIXED = 'fixed';
    const TIER  = 'tier';
    const FIXED_CUR = 'fixedcur';
    const TIER_CUR  = 'tiercur';

    const FIXED_LABEL = 'Fixed percent rate';
    const TIER_LABEL  = 'Tier percent rate';
    const FIXED_CUR_LABEL = 'Fixed amount (base currency)';
    const TIER_CUR_LABEL  = 'Tier amount (base currency)';

    public function toOptionArray()
    {
        $helper = $this->_getHelper();
        return array(array('value' => self::FIXED, 'label' => $helper->__(self::FIXED_LABEL)),
                     array('value' => self::TIER,  'label' => $helper->__(self::TIER_LABEL)),
            array('value' => self::FIXED_CUR,  'label' => $helper->__(self::FIXED_CUR_LABEL)),
            array('value' => self::TIER_CUR,  'label' => $helper->__(self::TIER_CUR_LABEL)));
    }
}

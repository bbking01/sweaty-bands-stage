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


class AW_Affiliate_Model_Source_Transaction_Profit_Type extends AW_Affiliate_Model_Source_Abstract
{
    const CUSTOMER_VISIT = 'trx_customer_visit';
    const CUSTOMER_PURCHASE = 'trx_customer_purchase';
    const ADMIN = 'trx_admin';

    const CUSTOMER_VISIT_LABEL = 'trx_customer_visit';
    const CUSTOMER_PURCHASE_LABEL = 'trx_customer_purchase';
    const ADMIN_LABEL = 'trx_admin';

    public function toOptionArray()
    {
        $helper = $this->_getHelper();
        return array(
            array('value' => self::CUSTOMER_VISIT, 'label' => $helper->__(self::CUSTOMER_VISIT_LABEL)),
            array('value' => self::CUSTOMER_PURCHASE, 'label' => $helper->__(self::CUSTOMER_PURCHASE_LABEL)),
            array('value' => self::ADMIN, 'label' => $helper->__(self::ADMIN_LABEL)),
        );
    }
}

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


class AW_Affiliate_Model_Source_Report_Type extends AW_Affiliate_Model_Source_Abstract
{
    const SALES = 'sales';
    const TRANSACTIONS = 'transactions';
    const TRAFFIC = 'traffic';

    const SALES_LABEL = 'Sales Report';
    const TRANSACTIONS_LABEL = 'Conversion Report';
    const TRAFFIC_LABEL = 'Traffic Source';

    public function toOptionArray()
    {
        $helper = $this->_getHelper();
        return array(
            array('value' => self::SALES, 'label' => $helper->__(self::SALES_LABEL)),
            array('value' => self::TRANSACTIONS, 'label' => $helper->__(self::TRANSACTIONS_LABEL)),
            array('value' => self::TRAFFIC, 'label' => $helper->__(self::TRAFFIC_LABEL))
        );
    }
}

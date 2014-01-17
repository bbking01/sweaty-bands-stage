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


class AW_Affiliate_Model_Source_Withdrawal_Status extends AW_Affiliate_Model_Source_Abstract
{
    const PENDING = 'pending';
    const PAID = 'paid';
    const REJECTED = 'rejected';
    const FAILED = 'failed';

    const PENDING_LABEL = 'Pending';
    const PAID_LABEL = 'Paid';
    const REJECTED_LABEL = 'Rejected';
    const FAILED_LABEL = 'Failed';

    const INITIAL_STATUS = self::PENDING;

    public function toOptionArray()
    {
        $helper = $this->_getHelper();
        return array(
            array('value' => self::PENDING, 'label' => $helper->__(self::PENDING_LABEL)),
            array('value' => self::PAID, 'label' => $helper->__(self::PAID_LABEL)),
            array('value' => self::REJECTED, 'label' => $helper->__(self::REJECTED_LABEL)),
            array('value' => self::FAILED, 'label' => $helper->__(self::FAILED_LABEL)),
        );
    }
}

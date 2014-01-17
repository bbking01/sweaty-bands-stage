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
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Autorelated_Model_Source_Resource_Collection_Order extends AW_Autorelated_Model_Source_Abstract
{
    const ASC_TITLE = 'Ascending';
    const DESC_TITLE = 'Descending';

    public function toOptionArray()
    {
        $helper = $this->_getHelper();
        return array(
            Varien_Data_Collection::SORT_ORDER_ASC => $helper->__(self::ASC_TITLE),
            Varien_Data_Collection::SORT_ORDER_DESC => $helper->__(self::DESC_TITLE)
        );
    }
}

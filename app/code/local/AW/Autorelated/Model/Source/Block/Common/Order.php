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


class AW_Autorelated_Model_Source_Block_Common_Order extends AW_Autorelated_Model_Source_Abstract
{
    const NONE = 0;
    const RANDOM = 1;
    const BY_ATTRIBUTE = 2;

    const NONE_TITLE = 'None';
    const RANDOM_TITLE = 'Random';
    const BY_ATTRIBUTE_TITLE = 'By Attribute';

    public function toOptionArray()
    {
        $_helper = $this->_getHelper();
        return array(
            self::NONE => $_helper->__(self::NONE_TITLE),
            self::RANDOM => $_helper->__(self::RANDOM_TITLE),
            self::BY_ATTRIBUTE => $_helper->__(self::BY_ATTRIBUTE_TITLE)
        );
    }
}

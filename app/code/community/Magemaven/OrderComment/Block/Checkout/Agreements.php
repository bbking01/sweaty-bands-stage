<?php
/**
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Magemaven
 * @package     Magemaven_OrderComment
 * @copyright   Copyright (c) 2011-2012 Sergey Storchay <r8@r8.com.ua>
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class Magemaven_OrderComment_Block_Checkout_Agreements extends Mage_Checkout_Block_Agreements
{
    /**
     * Override block template
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setTemplate('ordercomment/checkout/agreements.phtml');
        return parent::_toHtml();
    }
}
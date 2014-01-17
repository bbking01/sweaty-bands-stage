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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Customer_Widget extends AW_Points_Block_Customer_Reward_Summary implements Mage_Widget_Block_Interface
{

    protected function _construct()
    {
        if ($this->_isCustomer()) {
            return parent::_construct();
        }
    }

    protected function _toHtml()
    {
       return $this->_isCustomer() ?
            $this->setTemplate('aw_points/customer/widget.phtml')->renderView() : null;
       
    }

    protected function _isCustomer()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

}
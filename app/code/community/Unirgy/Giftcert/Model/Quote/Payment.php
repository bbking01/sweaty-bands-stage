<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Model_Quote_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'ugiftcert';
    protected $_formBlockType = 'ugiftcert/payment_form';
    protected $_infoBlockType = 'ugiftcert/payment_info';
    protected $_canUseInternal = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial     = true;

    public function isAvailable($quote=null)
    {
        return ($quote instanceof Mage_Sales_Model_Quote)
            && ($quote->getGiftcertCode())
            && ($quote->getBaseGrandTotal()==0);
    }

    /**
     * Override parent method, to allow for auto invoicing of fully paid gift certificate orders
     * @return string
     */
    public function getConfigPaymentAction()
    {
        if(in_array($this->getConfigData('order_status'), array(Mage_Sales_Model_Order::STATE_COMPLETE, Mage_Sales_Model_Order::STATE_PROCESSING))) {
            return 'authorize_capture';
        }
        return parent::getConfigPaymentAction();
    }


}

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
class Unirgy_Giftcert_CheckoutController extends Mage_Core_Controller_Front_Action
{
    public function removeAction()
    {
        $gc  = $this->getRequest()->getParam('gc');
        $session = Mage::getSingleton('checkout/session');
        $gcs = $session->getQuote()->getGiftcertCode();
        if ($gc && $gcs && stripos($gcs, $gc) !== false) {

            $gcsArr = array();
            foreach (explode(',', $gcs) as $gc1) {
                if (trim($gc1) !== $gc) {
                    $gcsArr[] = $gc1;
                }
            }

            $session->getQuote()->setGiftcertCode(join(',', $gcsArr))->save();
            $session->addSuccess(
                Mage::helper('ugiftcert')->__("Gift certificate '%s' was removed from your order.", $gc)
            );
        } else {
            $session->addError('Invalid request. Code mismatch.');
        }

        $this->_redirect('checkout/cart');
    }

    public function addAction()
    {
        /* @var $hlp Unirgy_Giftcert_Helper_Data */
        $hlp          = Mage::helper('ugiftcert');
        $code         = trim($this->getRequest()->getParam('cert_code'));
        $redirectBack = trim($this->getRequest()->getParam('current_action'));
        if ($code) {
            $session = Mage::getSingleton('checkout/session');
            $quote   = $session->getQuote();
            try {
                if ($hlp->addCertificate($code, $quote)) {
                    $session->addSuccess(
                        Mage::helper('ugiftcert')->__("Gift certificate '%s' was applied to your order.", $code)
                    );
                } else {
                    $session->addError($hlp->__("'%s' is not valid certificate code.", $code));
                }
            } catch (Unirgy_Giftcert_Exception_Coupon $gce) {
                $session->addError($gce->getMessage());
            } catch (Exception $e) {
                $session->addError($hlp->__("Gift certificate '%s' could not be applied to your order.", $code));
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect($redirectBack);
    }
}

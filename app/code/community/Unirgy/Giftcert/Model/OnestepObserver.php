<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-7-13
 * Time: 20:13
 * To change this template use File | Settings | File Templates.
 */

class Unirgy_Giftcert_Model_OnestepObserver
    extends Unirgy_Giftcert_Model_Observer
{
    /**
     * Catch GC codes applied in cart
     *
     * @param mixed $observer
     */
    public function controller_action_predispatch_onestepcheckout_ajax_add_coupon($observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        /* @var $hlp Unirgy_Giftcert_Helper_Data */
        $hlp    = Mage::helper('ugiftcert');

        $response = array(
            'success' => false,
            'error'   => false,
            'message' => false,
        );
        $code     = trim($action->getRequest()->getParam('code'));

        /* @var $cert Unirgy_Giftcert_Model_Cert */
        $cert = Mage::getModel('ugiftcert/cert')->load($code, 'cert_number');
        if ($action->getRequest()->getParam('remove') == 1) {
            try {
                $cert->removeFromQuote(Mage::getSingleton('checkout/session')->getQuote());
                $response['success'] = true;
                $response['message'] = $hlp->__('Gift certificate was canceled successfully.');
            } catch (Exception $e) {
                return;
            }
        } else {
            if ($cert->getId() && $cert->getStatus() == 'A' && $cert->getBalance() > 0) {
                try {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    if ($hlp->addCertificate($code, $quote)) {
                        $response['message'] = $hlp->__(
                            "Gift certificate '%s' was applied to your order.", $cert->getCertNumber()
                        );
                        $response['success'] = true;
                    }
                    /*
                     if(Mage::getStoreConfig('ugiftcert/default/use_conditions')) {
                        $valid = $hlp->validateConditions($cert, $quote);
                        if(!$valid) {
                            $response['error'] = true;
                            $response['message'] = $hlp->__("Gift certificate '%s' cannot be used with your cart items", $cert->getCertNumber());
                            return;
                        }
                    }
                    if(!$response['error']) { // if no error
                        $cert->addToQuote($quote);
                        $quote->collectTotals()->save();
                        $response['message'] = $hlp->__("Gift certificate '%s' was applied to your order.", $cert->getCertNumber());
                        $response['success'] = true;
                    }
                    */
                } catch (Unirgy_Giftcert_Exception_Coupon $gce) {
                    $response['message'] = $gce->getMessage();
                    $response['error']   = true;
                } catch (Mage_Core_Exception $e) {
                    $response['message'] = $hlp->__(
                        "Gift certificate '%s' could not be applied to your order. %s", $code, $e->getMessage()
                    );
                    $response['error']   = true;
                } catch (Exception $e) {
                    $response['message'] = $hlp->__(
                        "Gift certificate '%s' could not be applied to your order.", $cert->getCertNumber()
                    );
                    $response['error']   = true;
                }
            } else {
                return;
            }
        }
        if ($response['success'] || $response['error'] || $response['message']) {
            $this->loadShippingHtml($response);
            $this->loadPaymentHtml($response);
            $this->loadSummaryHtml($response);
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $action->getResponse()->setBody(Zend_Json::encode($response));
        }
    }

    protected function loadSummaryHtml(&$response)
    {
        // Add updated totals HTML to the output
        $html = Mage::app()->getLayout()
            ->createBlock('onestepcheckout/summary')
            ->setTemplate('onestepcheckout/summary.phtml')
            ->toHtml();

        $response['summary'] = $html;
        return $response;
    }

    protected function loadShippingHtml(&$response)
    {
        $html = Mage::app()->getLayout()
            ->createBlock('checkout/onepage_shipping_method_available')
            ->setTemplate('onestepcheckout/shipping_method.phtml')
            ->toHtml();

        $response['shipping_method'] = $html;
        return $response;
    }

    protected function loadPaymentHtml(&$response)
    {
        $html = Mage::app()->getLayout()
            ->createBlock('checkout/onepage_payment_methods', 'choose-payment-method')
            ->setTemplate('onestepcheckout/payment_method.phtml');

        if (Mage::helper('onestepcheckout')->isEnterprise() && Mage::helper('customer')->isLoggedIn()) {

            $customerBalanceBlock        = Mage::app()
                ->getLayout()
                ->createBlock('enterprise_customerbalance/checkout_onepage_payment_additional', 'customerbalance', array('template'=> 'onestepcheckout/customerbalance/payment/additional.phtml'));
            $customerBalanceBlockScripts = Mage::app()
                ->getLayout()
                ->createBlock('enterprise_customerbalance/checkout_onepage_payment_additional', 'customerbalance_scripts', array('template'=> 'onestepcheckout/customerbalance/payment/scripts.phtml'));

            $rewardPointsBlock        = Mage::app()
                ->getLayout()
                ->createBlock('enterprise_reward/checkout_payment_additional', 'reward.points', array('template'=> 'onestepcheckout/reward/payment/additional.phtml', 'before' => '-'));
            $rewardPointsBlockScripts = Mage::app()
                ->getLayout()
                ->createBlock('enterprise_reward/checkout_payment_additional', 'reward.scripts', array('template'=> 'onestepcheckout/reward/payment/scripts.phtml', 'after' => '-'));

            Mage::app()->getLayout()->getBlock('choose-payment-method')
                ->append($customerBalanceBlock)
                ->append($customerBalanceBlockScripts)
                ->append($rewardPointsBlock)
                ->append($rewardPointsBlockScripts);
        }

        if (Mage::helper('onestepcheckout')->isEnterprise()) {
            $giftcardScripts = Mage::app()
                ->getLayout()
                ->createBlock('enterprise_giftcardaccount/checkout_onepage_payment_additional', 'giftcardaccount_scripts', array('template'=> 'onestepcheckout/giftcardaccount/onepage/payment/scripts.phtml'));
            $html->append($giftcardScripts);
        }

        $response['payment_method'] = $html->toHtml();
        return $response;
    }

}

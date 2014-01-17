<?php
class VladimirPopov_WebFormsCRF_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    public function showCaptchaRegistration()
    {
        if (Mage::helper('webforms')->captchaAvailable())
            return Mage::getStoreConfig('webformscrf/captcha/registration');
        else return false;
    }

    public function showCaptchaCheckout()
    {
        if (Mage::helper('webforms')->captchaAvailable())
            return Mage::getStoreConfig('webformscrf/captcha/checkout');
        else return false;
    }

    public function customerActivationEnabled()
    {
        if (Mage::helper('core')->isModuleEnabled('Netzarbeiter_CustomerActivation')) return true;
        return false;
    }
}
?>

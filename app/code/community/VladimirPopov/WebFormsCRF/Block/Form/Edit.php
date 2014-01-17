<?php
class VladimirPopov_WebFormsCRF_Block_Form_Edit
    extends Mage_Customer_Block_Form_Edit
{
    public function getTemplate()
    {
        if (Mage::getStoreConfig('webformscrf/registration/enable') && Mage::getStoreConfig('webformscrf/registration/form')) return;

        $customer = Mage::helper('customer')->getCustomer();
        $group = Mage::getModel('customer/group')->load($customer->getGroupId());

        if ($group->getWebformId()) return;

        return parent::getTemplate();
    }

}
?>

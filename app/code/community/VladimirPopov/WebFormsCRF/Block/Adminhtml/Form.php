<?php
class VladimirPopov_WebFormsCRF_Block_Adminhtml_Form
    extends Mage_Adminhtml_Block_Customer_Edit_Form
{

    protected function _prepareForm()
    {
        parent::_prepareForm();
        $this->getForm()->setData('enctype', 'multipart/form-data');
        return $this;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $customer = Mage::registry('current_customer');
        $group = Mage::getModel('customer/group')->load($customer->getGroupId());
        $customerStoreId = 0;
        if ($customer->getId()) {
            $customerStoreId = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
        }
        $result = Mage::getModel('webforms/results');
        $webform = Mage::getModel('webforms/webforms');
        $webformId = Mage::getStoreConfig('webformscrf/registration/form', $customerStoreId);
        if ($group->getWebformId()) {
            $webformId = $group->getWebformId();
        }
        $webform->load($webformId);
        if ($customer->getData('result_id')) {
            $result->load($customer->getData('result_id'));
        }

        // add scripts
        if($webform->getLogic())
            $js = $this->getLayout()->createBlock('core/template', 'webformscrf_logic_'.$webform->getId(), array(
                'template' => 'webforms/logic.phtml',
                'result' => $result,
                'webform' => $webform,
                'prefix' => 'account'
            ));

        if(!empty($js))
            $this->getLayout()->getBlock('content')->append(
                $js
            );
    }

}
?>

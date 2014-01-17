<?php
class VladimirPopov_WebFormsCRF_Block_Adminhtml_Group
    extends Mage_Adminhtml_Block_Customer_Group_Edit_Form
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $form = $this->getForm();
        $customerGroup = Mage::registry('current_group');

        $fieldset = $form->addFieldset('webforms', array('legend' => Mage::helper('webforms')->__('Web-forms')));

        $default = array('0' => $this->__('- System default -'));

        $fieldset->addField('webform_id', 'select', array
        (
            'label' => Mage::helper('core')->__('Registration form'),
            'title' => Mage::helper('core')->__('Registration form'),
            'note' => Mage::helper('webformscrf')->__('Use Web-forms: Customer Registration Form widget to register customers from the CMS page'),
            'name' => 'webform_id',
            'required' => false,
            'values' => array_merge($default, Mage::getModel('webforms/webforms')->toOptionArray()),
        ));

        if (Mage::helper('webformscrf')->customerActivationEnabled())
            $fieldset->addField('crf_activation_status', 'select', array
            (
                'label' => Mage::helper('core')->__('Activate customers by default'),
                'title' => Mage::helper('core')->__('Activate customers by default'),
                'name' => 'crf_activation_status',
                'required' => false,
                'note' => Mage::helper('core')->__('Activate customers after registration by default'),
                'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            ));

        if (Mage::getSingleton('adminhtml/session')->getCustomerGroupData()) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getCustomerGroupData());
            Mage::getSingleton('adminhtml/session')->setCustomerGroupData(null);
        } else {
            $form->addValues($customerGroup->getData());
        }
    }
}
?>

<?php
/**
 * Certificates Adminhtml report filter form
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Petar
 */
class Unirgy_Giftcert_Block_Adminhtml_Report_Form
    extends Mage_Sales_Block_Adminhtml_Report_Filter_Form
{
    /**
     * Add fields to base fieldset which are general to sales reports
     *
     * @return Mage_Sales_Block_Adminhtml_Report_Filter_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $actionUrl = $this->getUrl('*/*/report');
        $form->setAction($actionUrl);
        return $this;
    }
}

<?php

class Unirgy_Giftcert_Block_Adminhtml_Sales_Order_Totals
    extends Mage_Adminhtml_Block_Sales_Order_Totals
{
    protected function _initTotals()
    {
        parent::_initTotals();

        if (((float)$this->getSource()->getGiftcertAmount()) != 0) {
            if ($this->getSource()->getGiftcertCode()) {
                $giftcertLabel = $this->__('Gift Certificates (%s)', $this->getSource()->getGiftcertCode());
            } else {
                $giftcertLabel = $this->__('Gift Certificates');
            }
            $this->_totals['giftcert'] = new Varien_Object(array(
                'code'      => 'giftcert',
                'value'     => -$this->getSource()->getGiftcertAmount(),
                'base_value'=> -$this->getSource()->getBaseGiftcertAmount(),
                'label'     => $giftcertLabel
            ));
        }
        if (((float)$this->getSource()->getGiftcertAmountInvoiced()) != 0) {
            $this->_totals['giftcert_invoiced'] = new Varien_Object(array(
                'code'      => 'giftcert_invoiced',
                'value'     => $this->getSource()->getGiftcertAmountInvoiced(),
                'base_value'=> $this->getSource()->getBaseGiftcertAmountInvoiced(),
                'label'     => $this->__('Gift Certificates Invoiced'),
                'strong'    => true,
                'area'      => 'footer',
            ));
        }
        if (((float)$this->getSource()->getGiftcertAmountCredited()) != 0) {
            $this->_totals['giftcert_credited'] = new Varien_Object(array(
                'code'      => 'giftcert_credited',
                'value'     => $this->getSource()->getGiftcertAmountCredited(),
                'base_value'=> $this->getSource()->getBaseGiftcertAmountCredited(),
                'label'     => $this->__('Gift Certificates Credited'),
                'strong'    => true,
                'area'      => 'footer',
            ));
        }
    }
}
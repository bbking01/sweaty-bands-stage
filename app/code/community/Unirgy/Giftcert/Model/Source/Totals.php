<?php

class Unirgy_Giftcert_Model_Source_Totals
    extends Mage_Sales_Model_Quote_Address
{
    public function toOptionArray()
    {
        $options = array();
        $this->setQuote(Mage::getSingleton('checkout/session')->getQuote());
        $totals = $this->getTotalModels();
        foreach ($totals as $model) {
            $k = $model->getCode();
            if (in_array($k, array('ugiftcert', 'grand_total',''))) {
                continue;
            }
            $lbl = $model->getLabel()? $model->getLabel(): $k;
            $options[] = array(
                'value' => $k,
                'label' => $lbl
            );
        }
        return $options;
    }
}
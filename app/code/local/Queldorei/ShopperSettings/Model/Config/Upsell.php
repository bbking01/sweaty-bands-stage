<?php

class Queldorei_ShopperSettings_Model_Config_Upsell
{
    public function toOptionArray()
    {
        return array(
            array(
                'value'=>'never',
                'label' => Mage::helper('shoppersettings')->__('Never Replace Upsell Products')),
            array(
                'value'=>'always',
                'label' => Mage::helper('shoppersettings')->__('Always Replace Upsell Products')),
            array(
                'value'=>'only',
                'label' => Mage::helper('shoppersettings')->__('Replace Only if No Upsell Products')),
        );
    }
}
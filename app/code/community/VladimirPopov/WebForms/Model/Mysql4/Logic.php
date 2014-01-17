<?php
class VladimirPopov_WebForms_Model_Mysql4_Logic
    extends VladimirPopov_WebForms_Model_Mysql4_Abstract
{
    const ENTITY_TYPE = 'logic';

    public function getEntityType()
    {
        return self::ENTITY_TYPE;
    }

    public function _construct()
    {
        $this->_init('webforms/logic', 'id');
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->setData('value', unserialize($object->getData('value_serialized')));
        $object->setData('target', unserialize($object->getData('target_serialized')));

        Mage::dispatchEvent('webforms_logic_after_load', array('logic' => $object));

        return parent::_afterLoad($object);
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (is_array($object->getData('value'))) $object->setData('value_serialized', serialize($object->getData('value')));
        if (is_array($object->getData('target'))) $object->setData('target_serialized', serialize($object->getData('target')));

        Mage::dispatchEvent('webforms_logic_before_save', array('logic' => $object));

        return parent::_beforeSave($object);
    }
}
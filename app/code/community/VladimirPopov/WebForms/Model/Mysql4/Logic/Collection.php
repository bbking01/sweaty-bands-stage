<?php
class VladimirPopov_WebForms_Model_Mysql4_Logic_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('webforms/logic');
    }

    protected function _afterLoad()
    {
        // unserialize
        foreach ($this as $item) {
            $item->setData('value', unserialize($item->getData('value_serialized')));
            $item->setData('target', unserialize($item->getData('target_serialized')));
        }

        $store_id = $this->getResource()->getStoreId();
        if ($store_id) {
            foreach ($this as $item) {
                $store = Mage::getModel('webforms/store')->search($store_id, $this->getResource()->getEntityType(), $item->getId());
                $store_data = $store->getStoreData();
                if ($store_data) {
                    foreach ($store_data as $key => $val) {
                        $item->setData($key, $val);
                    }
                }
            }
        }

        return parent::_afterLoad();
    }

    public function addWebformFilter($webform_id)
    {
        $this->getSelect()
            ->join(array('fields' => $this->getTable('webforms/fields')), 'main_table.field_id = fields.id', array('main_table.*', 'fields.name', 'fields.webform_id'))
            ->group('main_table.id');

        $this->getSelect()
            ->where("fields.webform_id = '{$webform_id}'");

        return $this;
    }

}
<?php
class VladimirPopov_WebFormsCRF_Block_Widget_Form
    extends VladimirPopov_WebFormsCRF_Block_Form
    implements Mage_Widget_Block_Interface
{
    public function getFormData()
    {
        $data = parent::getFormData();

        if ($this->getData('webform_id')) {
            $data['webform_id'] = $this->getData('webform_id');
        }

        return $data;
    }

    public function getWebformId()
    {
        if ($this->getData('webform_id')) return $this->getData('webform_id');

        return parent::getWebformId();
    }

    public function isDirectAvailable()
    {
        return true;
    }

    protected function isEnabled()
    {
        return true;
    }

    public function getShowAddressFields()
    {
        return $this->getData('address');
    }
}


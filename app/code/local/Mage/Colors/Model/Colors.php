<?php

class Mage_Colors_Model_Colors extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('colors/colors');
    }
}
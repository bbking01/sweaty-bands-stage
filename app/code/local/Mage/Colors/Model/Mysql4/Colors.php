<?php

class Mage_Colors_Model_Mysql4_Colors extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the colors_id refers to the key field in your database table.
        $this->_init('colors/colors', 'colors_id');
    }
}
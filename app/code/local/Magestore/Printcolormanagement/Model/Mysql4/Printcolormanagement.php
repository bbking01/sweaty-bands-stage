<?php

class Magestore_Printcolormanagement_Model_Mysql4_Printcolormanagement extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the bannerslider_id refers to the key field in your database table.
        $this->_init('printcolormanagement/printcolormanagement', 'color_id');
    }
}
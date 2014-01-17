<?php

class Magestore_Clipartmanagement_Model_Mysql4_Clipartcategory extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the bannerslider_id refers to the key field in your database table.
        $this->_init('clipartmanagement/clipartcategory', 'clipart_cat_id');
    }
}
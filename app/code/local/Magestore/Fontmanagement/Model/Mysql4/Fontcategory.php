<?php

class Magestore_Fontmanagement_Model_Mysql4_Fontcategory extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the bannerslider_id refers to the key field in your database table.
        $this->_init('fontmanagement/fontcategory', 'font_cat_id');
    }
}
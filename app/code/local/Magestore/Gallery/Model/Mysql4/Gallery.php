<?php

class Magestore_Gallery_Model_Mysql4_Gallery extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the gallery_id refers to the key field in your database table.
        $this->_init('gallery/gallery', 'gallery_id');
    }
}
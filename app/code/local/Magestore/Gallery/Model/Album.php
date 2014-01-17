<?php

class Magestore_Gallery_Model_Album extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('gallery/album');
    }
}
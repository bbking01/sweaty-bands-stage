<?php
/**
* @author Amasty Team
* @copyright Copyright (c) Amasty (http://www.amasty.com)
* @package Amasty_Perm
*/
class Amasty_Perm_Model_Login extends Mage_Catalog_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amperm/login');
    }

    public function truncate()
    {
        $this->getResource()->truncate();
        return $this;
    }
}
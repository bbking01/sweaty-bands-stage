<?php
/**
* @author Amasty Team
* @copyright Copyright (c) Amasty (http://www.amasty.com)
* @package Amasty_Perm
*/
class Amasty_Perm_Model_Mysql4_Login extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('amperm/login', 'login_id');
    }

    public function truncate()
    {
        $this->_getWriteAdapter()->truncate($this->getMainTable());
    }
}
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-11-14
 * Time: 23:14
 */
 
class Unirgy_Giftcert_Model_Config_Data
    extends Mage_Adminhtml_Model_Config_Data
{
    public function prepare()
    {
        $this->_validate();
        $this->_getScope();
        return $this;
    }
}

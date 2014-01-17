<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 */
class Unirgy_Giftcert_Model_Pdf_Model
 extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ugiftcert/pdf');
        return parent::_construct();
    }

}

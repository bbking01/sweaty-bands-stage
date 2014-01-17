<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 */
class Unirgy_Giftcert_Model_Mysql4_Pdf_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ugiftcert/pdf_model');
        parent::_construct();
    }
}

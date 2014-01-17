<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 */
class Unirgy_Giftcert_Model_Mysql4_Pdf
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('ugiftcert/pdf', 'template_id');
    }
}

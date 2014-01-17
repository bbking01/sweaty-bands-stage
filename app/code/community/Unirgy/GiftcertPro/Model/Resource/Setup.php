<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 */
class Unirgy_GiftcertPro_Model_Resource_Setup
    extends Unirgy_Giftcert_Model_Resource_Setup
{
    protected function _getFileName($version)
    {
        $file = dirname(__FILE__) . DS . 'Setup' . DS . $version . '.php';
        return $file;
    }
}

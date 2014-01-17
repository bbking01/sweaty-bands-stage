<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 */
class Unirgy_Giftcert_Model_Resource_Setup
    extends Mage_Core_Model_Resource_Setup
{
    public function install()
    {
        // todo implement install in setup object
    }

    public function remove()
    {
        // todo implement remove module (database entries only)
    }

    public function updateTo($version)
    {
        $setup = $this->getSetupModel($version);
        $setup->update();
    }

    public function rollBackFrom($version)
    {
        $setup = $this->getSetupModel($version);
        $setup->rollBack();
    }

    /**
     * @param $version
     * @return Unirgy_Giftcert_Model_Resource_Setup_Interface
     */
    public function getSetupModel($version)
    {
        $file = $this->getUpdateFile($version);
        require_once $file;

        $class = get_class($this) . '_' . $version;
        if(!class_exists($class, false)){
            Mage::throwException("Class for version $version not found");
        }

        $model = new $class($this);

        if(!$model instanceof Unirgy_Giftcert_Model_Resource_Setup_Interface){
            Mage::throwException("Version setup should implement Unirgy_Giftcert_Model_Resource_Setup_Interface");
        }
        return $model;
    }

    public function getUpdateFile($version)
    {
        $file = $this->_getFileName($version);
        if(!file_exists($file)){
            Mage::throwException("File for version $version not found");
        }
        return $file;
    }

    protected function _getFileName($version)
    {
        $file = dirname(__FILE__) . DS . 'Setup' . DS . $version . '.php';
        return $file;
    }

    public function log($e, $message = null)
    {
        if(null != $message){
            Mage::log($message, Zend_Log::INFO, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        }
        if($e instanceof Exception){
            Mage::log($e->getMessage(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            Mage::log($e->getTraceAsString(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        } else if ( is_string($e)) {
            Mage::log($message, Zend_Log::INFO, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        }
    }
}

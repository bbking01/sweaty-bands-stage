<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 */
class Unirgy_GiftcertPro_Model_Resource_Setup_100
    implements Unirgy_Giftcert_Model_Resource_Setup_Interface
{
    /**
     * @var Unirgy_Giftcert_Model_Resource_Setup
     */
    protected $setup;

    public function __construct(Unirgy_Giftcert_Model_Resource_Setup $setup)
    {
        $this->setup = $setup;
    }

    public function update()
    {
        try {
            $this->addProductAttributes();
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            Mage::log($e->getTraceAsString(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
        }
        return $this;
    }

    /**
     * @return Unirgy_GiftcertPro_Model_Resource_Setup_100
     */
    public function addProductAttributes()
    {
        $personalizationAttribute = 'ugiftcert_personalization';
        $eav                      = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
        $entityTypeId             = $eav->getEntityTypeId('catalog_product');

        $eav->addAttribute($entityTypeId, $personalizationAttribute, array(
            'type'             => 'text',
            'input'            => 'text',
            'label'            => 'GC Personalization Options',
            'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'user_defined'     => 1,
            'apply_to'         => 'ugiftcert',
            'required'         => 0,
            'group'            => 'GC Settings',
            'input_renderer'   => 'ugiftcertpro/product_personalization'
        ));

        return $this;
    }

    public function rollBack()
    {
        $this->resetProductAttributes();
    }

    public function resetProductAttributes()
    {
        $personalizationAttribute = 'ugiftcert_personalization';
        $eav                      = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
        $entityTypeId             = $eav->getEntityTypeId('catalog_product');
        $eav->removeAttribute($entityTypeId, $personalizationAttribute);
    }

    public function getTable($table)
    {
        return $this->setup->getTable($table);
    }

    public function getConnection()
    {
        return $this->setup->getConnection();
    }

    /**
     * @return Unirgy_Giftcert_Model_Resource_Setup
     */
    public function getSetupModel()
    {
        return $this->setup;
    }
}

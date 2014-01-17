<?php

class Unirgy_GiftcertPro_Helper_Data
    extends Unirgy_Giftcert_Helper_Data
{
    /**
     * Load personalization settings from POST
     * If parameter serialize is passed as true, return serialized value.
     *
     * @param bool $serialize
     * @return array|int|null|string
     */
    public function preparePersonalizeSettings($serialize = true)
    {
        return Mage::helper('ugiftcertpro/protected')->preparePersonalizeSettings($this, $serialize);
    }

    public function processOptionsImages($configDataModel)
    {
        $images = array();
        $el = 'ugiftcert_personalization';
        if (isset($_FILES[$el]['name']) && is_array($_FILES[$el]['name'])) {
            $images = $this->_processUploadedImages($configDataModel, 'personalization', $el);
        }

        return $images;
    }
}

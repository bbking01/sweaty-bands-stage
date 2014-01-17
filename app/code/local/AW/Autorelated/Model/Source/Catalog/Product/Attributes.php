<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Autorelated_Model_Source_Catalog_Product_Attributes extends AW_Autorelated_Model_Source_Abstract
{
    protected function _cmpLabels($a, $b)
    {
        return strcmp($a['label'], $b['label']);
    }

    public function toOptionArray()
    {
        $attributes = array();
        $attributesCollection = Mage::getResourceSingleton('catalog/product')
            ->loadAllAttributes()
            ->getAttributesByCode();
        foreach ($attributesCollection as $attribute) {
            $_frontendLabel = $attribute->getFrontendLabel();
            $_attrCode = $attribute->getAttributeCode();
            if (strpos($_attrCode, 'quote_') === 0) {
                continue;
            }
            if ($_frontendLabel) {
                $attributes[] = array('value' => $_attrCode, 'label' => $_frontendLabel);
            }
        }
        $_unsetAttributes = array(
            'attribute_set_id',
            'category_ids'
        );
        foreach ($_unsetAttributes as $attribute) {
            if (isset($attributes[$attribute])) {
                unset($attributes[$attribute]);
            }
        }
        usort($attributes, array($this, '_cmpLabels'));
        return $attributes;
    }
}

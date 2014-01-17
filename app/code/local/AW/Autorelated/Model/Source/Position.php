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


class AW_Autorelated_Model_Source_Position extends AW_Autorelated_Model_Source_Abstract
{
    const INSIDE_PRODUCT_PAGE = 1;
    const INSTEAD_NATIVE_RELATED_BLOCK = 2;
    const UNDER_NATIVE_RELATED_BLOCK = 3;
    const BEFORE_CONTENT = 4;
    const CUSTOM = 0;
    const REPLACE_CROSSSELS_BLOCK = 5;

    const INSIDE_PRODUCT_PAGE_LABEL = 'Inside product page';
    const INSTEAD_NATIVE_RELATED_BLOCK_LABEL = 'Instead native related block';
    const UNDER_NATIVE_RELATED_BLOCK_LABEL = 'Under native related block';
    const BEFORE_CONTENT_LABEL = 'Before content';
    const CUSTOM_LABEL = 'Custom';
    const REPLACE_CROSSSELS_BLOCK_LABEL = 'Replace Cross-Sells Block';

    const INSIDE_PRODUCT_PAGE_SHORT_LABEL = 'Inside product';
    const INSTEAD_NATIVE_RELATED_BLOCK_SHORT_LABEL = 'Instead native';
    const UNDER_NATIVE_RELATED_BLOCK_SHORT_LABEL = 'Under native';
    const BEFORE_CONTENT_SHORT_LABEL = 'Before content';
    const CUSTOM_SHORT_LABEL = 'Custom';
    const REPLACE_CROSSSELS_BLOCK_SHORT_LABEL = 'Replace Cross-Sells';

    public function toOptionArray($blockType = AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK)
    {
        $_helper = $this->_getHelper();
        $result = array(array('value' => self::BEFORE_CONTENT, 'label' => $_helper->__(self::BEFORE_CONTENT_LABEL)));
        switch ($blockType) {
            case AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK:
                array_push(
                    $result,
                    array('value' => self::INSTEAD_NATIVE_RELATED_BLOCK, 'label' => $_helper->__(self::INSTEAD_NATIVE_RELATED_BLOCK_LABEL)),
                    array('value' => self::UNDER_NATIVE_RELATED_BLOCK, 'label' => $_helper->__(self::UNDER_NATIVE_RELATED_BLOCK_LABEL)),
                    array('value' => self::INSIDE_PRODUCT_PAGE, 'label' => $_helper->__(self::INSIDE_PRODUCT_PAGE_LABEL))
                );
                break;
            case AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK:
                array_push(
                    $result,
                    array('value' => self::REPLACE_CROSSSELS_BLOCK, 'label' => $_helper->__(self::REPLACE_CROSSSELS_BLOCK_LABEL))
                );
                break;
        }
        $result[] = array('value' => self::CUSTOM, 'label' => $_helper->__(self::CUSTOM_LABEL));
        return $result;
    }

    public function getOptionArray()
    {
        $_helper = $this->_getHelper();
        return array(
            self::INSIDE_PRODUCT_PAGE => $_helper->__(self::INSIDE_PRODUCT_PAGE_SHORT_LABEL),
            self::INSTEAD_NATIVE_RELATED_BLOCK => $_helper->__(self::INSTEAD_NATIVE_RELATED_BLOCK_SHORT_LABEL),
            self::UNDER_NATIVE_RELATED_BLOCK => $_helper->__(self::UNDER_NATIVE_RELATED_BLOCK_SHORT_LABEL),
            self::BEFORE_CONTENT => $_helper->__(self::BEFORE_CONTENT_SHORT_LABEL),
            self::CUSTOM => $_helper->__(self::CUSTOM_SHORT_LABEL),
            self::REPLACE_CROSSSELS_BLOCK => $_helper->__(self::REPLACE_CROSSSELS_BLOCK_SHORT_LABEL)
        );
    }
}

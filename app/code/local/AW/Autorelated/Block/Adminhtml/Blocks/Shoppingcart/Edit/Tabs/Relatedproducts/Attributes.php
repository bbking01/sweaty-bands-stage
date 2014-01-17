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


class AW_Autorelated_Block_Adminhtml_Blocks_Shoppingcart_Edit_Tabs_Relatedproducts_Attributes
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /** @var $_addButtonBlock Mage_Adminhtml_Block_Widget_Button */
    protected $_addButtonBlock = null;
    protected $_productAttributes = null;
    protected $_attributeConditions = null;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setTemplate('aw_autorelated/render/shoppingcart/attributes.phtml')
            ->setElement($element);
        return $this->toHtml();
    }

    public function getAddButtonHtml()
    {
        if ($this->_addButtonBlock === null) {
            $this->_addButtonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => $this->__('Add Attribute'),
                    'onclick' => 'attributesControl.addItem()',
                    'class' => 'add'
                )
            );
        }
        return $this->_addButtonBlock->toHtml();
    }

    public function getProductAttributes()
    {
        if ($this->_productAttributes === null) {
            $this->_productAttributes = Mage::getModel('awautorelated/source_catalog_product_attributes')->toShortOptionArray();
        }
        return $this->_productAttributes;
    }

    public function getAttributeConditions()
    {
        if ($this->_attributeConditions === null) {
            $this->_attributeConditions = Mage::getModel('awautorelated/source_block_shoppingcart_attributes_condition')->toShortOptionArray();
        }
        return $this->_attributeConditions;
    }
}

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


class AW_Autorelated_Block_Adminhtml_Blocks_Category_Edit_Tabs_Currentlyviewed extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_form = new Varien_Data_Form();
        $this->setForm($_form);
        $_data = Mage::helper('awautorelated/forms')->getFormData($this->getRequest()->getParam('id'));
        if (!is_object($_data))
            $_data = new Varien_Object($_data);

        $_fieldset = $_form->addFieldset('currently_viewed', array(
            'legend' => $this->__('Currently Viewed Category')
        ));

        if ($_data->getData('currently_viewed') instanceof Varien_Object) {
            if ($_data->getData('currently_viewed')->getData('area'))
                $_data->setData('currently_viewed_categories_area', $_data->getData('currently_viewed')->getData('area'));
            if ($_data->getData('currently_viewed')->getData('category_ids'))
                $_data->setData('category_ids', $_data->getData('currently_viewed')->getData('category_ids'));
        }

        $_fieldset->addField('currently_viewed_categories_area', 'select', array(
            'label' => $this->__('Categories'),
            'title' => $this->__('Categories'),
            'name' => 'currently_viewed[area]',
            'values' => array(
                array('value' => 1, 'label' => $this->__('All')),
                array('value' => 2, 'label' => $this->__('Custom'))
            )
        ));

        $_fieldset->addField('gridcontainer_categories', 'note', array(
            'label' => $this->__('Select Categories'),
            'text' => Mage::getSingleton('core/layout')->createBlock('awautorelated/adminhtml_blocks_category_edit_tabs_currentlyviewed_categoriesgrid')->toHtml()
        ));

        $_form->setValues($_data);
    }
}

<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Groups Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @author     Webtex Dev Team <dev@webtex.com>
 */
class Webtex_CustomerGroupsPrice_Block_Adminhtml_Customer_Group_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * Prepare form for render
	 */
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		$form = new Varien_Data_Form();
		$customerGroup = Mage::registry('current_group');
		$websiteId = Mage::app()->getStore(Mage::app()->getRequest()->getParam('store'))->getWebsiteId();

		$fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('customer')->__('Group Information')));

		$name = $fieldset->addField('customer_group_code', 'text',
			array(
				'name'  => 'code',
				'label' => Mage::helper('customer')->__('Group Name'),
				'title' => Mage::helper('customer')->__('Group Name'),
				'class' => 'required-entry',
				'required' => true,
			)
		);

		if ($customerGroup->getId()==0 && $customerGroup->getCustomerGroupCode() ) {
			$name->setDisabled(true);
		}

		$fieldset->addField('tax_class_id', 'select',
			array(
				'name'  => 'tax_class',
				'label' => Mage::helper('customer')->__('Tax Class'),
				'title' => Mage::helper('customer')->__('Tax Class'),
				'class' => 'required-entry',
				'required' => true,
				'values' => Mage::getSingleton('tax/class_source_customer')->toOptionArray()
			)
		);

        if($customerGroup->getId()){
            $fieldset->addField('price', 'text',
                array(
                    'name'  => 'price',
                    'label' => Mage::helper('customergroupsprice')->__('Group Price'),
                    'title' => Mage::helper('customergroupsprice')->__('Group Price')
                )
            );

            $fieldset->addField('price_type', 'select',
                array(
                    'name'  => 'price_type',
                    'label' => Mage::helper('customergroupsprice')->__('Price Type'),
                    'title' => Mage::helper('customergroupsprice')->__('Price Type'),
                    'values'=> array(array('value' => 1, 'label' => 'Fixed'), array('value' => 2, 'label' => 'Percent'))
                )
            );

            if (!is_null($customerGroup->getId())) {
                // If edit add id
                $form->addField('id', 'hidden',
                    array(
                        'name'  => 'id',
                        'value' => $customerGroup->getId(),
                    )
                );
            }

            $prices = Mage::getModel('customergroupsprice/globalprices')->loadPrice($customerGroup->getId(),$websiteId);
            $form->addValues(array('price' => $prices->getPrice(), 'price_type' => $prices->getPriceType()));
        }

		if( Mage::getSingleton('adminhtml/session')->getCustomerGroupData() ) {
			$form->addValues(Mage::getSingleton('adminhtml/session')->getCustomerGroupData());
			Mage::getSingleton('adminhtml/session')->setCustomerGroupData(null);
		} else {
			$form->addValues($customerGroup->getData());
		}

		$form->setUseContainer(true);
		$form->setId('edit_form');
		$form->setAction($this->getUrl('*/*/save'));
		$this->setForm($form);
	}
}

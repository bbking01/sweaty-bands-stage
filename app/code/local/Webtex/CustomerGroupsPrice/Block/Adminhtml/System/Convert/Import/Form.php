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

class Webtex_CustomerGroupsPrice_Block_Adminhtml_System_Convert_Import_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('customergroupsprice')->__('Import Settings')));

        $fieldset->addField('file', 'file', array(
                'name'  	=> 'file',
                'label'		=> Mage::helper('customergroupsprice')->__('Path to file for Prices'),
                'title' 	=> Mage::helper('customergroupsprice')->__('Path to file for Prices'),
				'required'	=> true
            )
        );

        $fieldset->addField('file_s', 'file', array(
                'name'  	=> 'file_s',
                'label'		=> Mage::helper('customergroupsprice')->__('Path to file for Special Prices'),
                'title' 	=> Mage::helper('customergroupsprice')->__('Path to file for Special Prices'),
				'required'	=> true
            )
        );

		$fieldset->addField('delimiter', 'text', array(
                'name'  	=> 'delimiter',
                'label' 	=> Mage::helper('customergroupsprice')->__('Value delimiter'),
                'title' 	=> Mage::helper('customergroupsprice')->__('Value delimiter'),
				'required'	=> true
            )
        );

		$fieldset->addField('enclosure', 'text', array(
                'name'  	=> 'enclosure',
                'label' 	=> Mage::helper('customergroupsprice')->__('Enclose Values In'),
					'title' => Mage::helper('customergroupsprice')->__('Enclose Values In'),
				'required'	=> true
            )
        );

		$importConfig = Mage::getModel('customergroupsprice/convert')->loadByAction('import');

		$form->setValues($importConfig->getData());
        $form->setAction($this->getUrl('*/convert/saveImport'));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');
		$form->setEnctype('multipart/form-data');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}

<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_QUICKBUY
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

class Itoris_QuickBuy_Block_Admin_Config_Edit_Form extends Mage_Adminhtml_Block_System_Config_Form {

	protected function _prepareForm() {
		try {
			$defaultSettings = Mage::getModel('itoris_quickbuy/settings');
			$defaultSettings->load($this->getWebsiteId(), $this->getStoreId());
		} catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
		}
		$useWebsite = (bool)$this->getStoreId();
		
		if (!$useWebsite) {
			$useDefault = (bool)$this->getWebsiteId();
		} else {
			$useDefault = false;
		}
        $form = new Varien_Data_Form();
		$form->setStoreId($this->getStoreId());
		$form->setWebsiteId($this->getWebsiteId());
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>$this->__('Settings')));
		$fieldset->addField('enable', 'select',
							array(
                				'name'  => 'settings[enable][value]',
                				'label' => $this->__('Extension Enabled'),
                				'title' => $this->__('Extension Enabled'),
                				'required' => true,
								'values' => array(
									array(
										'label' => $this->__('Yes'),
										'value' => Itoris_QuickBuy_Model_Settings::ENABLED,
									),
									array(
										'label' => $this->__('No'),
										'value' => Itoris_QuickBuy_Model_Settings::DISABLED,
									),
								),
								'use_default' => $useDefault,
								'use_website' => $useWebsite,
								'use_parent_value' => $defaultSettings->isParentValue('enable', $useWebsite),
							)
        )->getRenderer()->setTemplate('itoris/quickbuy/config/form/element.phtml');

		$fieldset->addField('search_engine', 'select',
			array(
				'name'  => 'settings[search_engine][value]',
				'label' => $this->__('Search Engine'),
				'title' => $this->__('Search Engine'),
				'required' => true,
				'values' => array(
					array(
						'label' => $this->__('Progressive Cache-based'),
						'value' => Itoris_QuickBuy_Model_Settings::SEARCH_ENGINE_CACHE,
					),
					array(
						'label' => $this->__('Standard SQL-based'),
						'value' => Itoris_QuickBuy_Model_Settings::SEARCH_ENGINE_SQL,
					),
				),
				'use_default' => $useDefault,
				'use_website' => $useWebsite,
				'use_parent_value' => $defaultSettings->isParentValue('search_engine', $useWebsite),
				'after_element_html' => '<div id="cache_time_box" style="margin-top:5px;'. ($defaultSettings->getSearchEngine() != Itoris_QuickBuy_Model_Settings::SEARCH_ENGINE_CACHE ? 'display:none;' : '') .'">
											<input type="text" class="validate-digits" value="'. $defaultSettings->getCacheLifetime() .'" name="settings[cache_lifetime][value]"
												' . ((($useDefault || $useWebsite) && $defaultSettings->isParentValue('cache_lifetime', $useWebsite)) ? 'disabled="disabled"' : '') .'
											/>
											<p class="note"><span>' . $this->__('cache lifetime') . ' (' . $this->__('min') . ')' . '</span></p>
										</div>',
				'onchange' => "if (this.value == " . Itoris_QuickBuy_Model_Settings::SEARCH_ENGINE_CACHE . ") {"
							. "$('cache_time_box').show();toogleItorisElement('show_grouped_instead_of_simple', false); } else { $('cache_time_box').hide();toogleItorisElement('show_grouped_instead_of_simple', true) }",
			)
		);

		$yesNoValues = array(
			array(
				'label' => $this->__('Yes'),
				'value' => 1,
			),
			array(
				'label' => $this->__('No'),
				'value' => 0,
			),
		);

		$fieldset->addField('use_catalog_search_terms', 'select', array(
			'label'            => $this->__('Use search synonyms from Catalog -> Search Terms'),
			'title'            => $this->__('Use search synonyms from Catalog -> Search Terms'),
			'name'             => 'settings[use_catalog_search_terms][value]',
			'values'           => $yesNoValues,
			'use_default'      => $useDefault,
			'use_website'      => $useWebsite,
			'use_parent_value' => $defaultSettings->isParentValue('use_catalog_search_terms', $useWebsite),
		));

		$fieldset->addField('default_product_ids', 'text', array(
			'label'            => $this->__('Product IDs shown by default'),
			'title'            => $this->__('Product IDs shown by default'),
			'name'             => 'settings[default_product_ids][value]',
			'note'             => $this->__('comma separated'),
			'use_default'      => $useDefault,
			'use_website'      => $useWebsite,
			'use_parent_value' => $defaultSettings->isParentValue('default_product_ids', $useWebsite),
		));

		$fieldset->addField('show_not_visible_products', 'select', array(
			'label'            => $this->__('Show "Not Visible Individually" products in search results'),
			'title'            => $this->__('Show "Not Visible Individually" products in search results'),
			'name'             => 'settings[show_not_visible_products][value]',
			'values'           => $yesNoValues,
			'use_default'      => $useDefault,
			'use_website'      => $useWebsite,
			'use_parent_value' => $defaultSettings->isParentValue('show_not_visible_products', $useWebsite),
		));

		$fieldset->addField('show_grouped_instead_of_simple_hidden', 'hidden', array(
			'value' => $defaultSettings->getShowGroupedInsteadOfSimple(),
			'name'  => 'settings[show_grouped_instead_of_simple][value]',
		));

		$fieldset->addField('show_grouped_instead_of_simple', 'select', array(
			'label'            => $this->__('Show Grouped products instead of simple'),
			'title'            => $this->__('Show Grouped products instead of simple'),
			'name'             => 'settings[show_grouped_instead_of_simple][value]',
			'values'           => $yesNoValues,
			'use_default'      => $useDefault,
			'use_website'      => $useWebsite,
			'use_parent_value' => $defaultSettings->isParentValue('show_grouped_instead_of_simple', $useWebsite),
			'disabled'         => $defaultSettings->getSearchEngine() == Itoris_QuickBuy_Model_Settings::SEARCH_ENGINE_SQL,
			'note'             => $this->__('Works with progressive search method only'),
		));

		$fieldset->addField('info_url', 'label', array(
			'label' => $this->__('Quick Order Form\'s frontend URL') . ":",
			'title' => $this->__('Quick Order Form\'s frontend URL') . ":",
		));

        $form->setValues($defaultSettings->getDefaultData());

        $form->setAction($this->getUrl('*/*/save', array( 'website_id' => $this->getWebsiteId(), 'store_id' => $this->getStoreId())));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }

	/**
	 * Retrieve store id by store code from the request
	 *
	 * @return int
	 */
	public function getStoreId() {
		if ($this->getStoreCode()) {
            return Mage::app()->getStore($this->getStoreCode())->getId();
        }
		return 0;
	}

	/**
	 * Retrieve website id by website code from the request
	 *
	 * @return int
	 */
	protected function getWebsiteId() {
		if ($this->getWebsiteCode()) {
            return Mage::app()->getWebsite($this->getWebsiteCode())->getId();
        }
		return 0;
	}
}
?>
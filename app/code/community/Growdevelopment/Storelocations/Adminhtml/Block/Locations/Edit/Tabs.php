<?php
/**
 * Grow Development - Store Locations Pro
 *
 * @category   Growdevelopment
 * @package    Growdevelopment_StoreLocations
 * @copyright  Copyright (c) 2012 Grow Development (http://www.growdevelopment.com)
 * @license    http://www.growdevelopment.com/docs/eula.txt
 * @author     Daniel Espinoza <modules@growdevelopment.com>
 *
 */
class Growdevelopment_Storelocations_Adminhtml_Block_Locations_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('location_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('growdevstorelocations')->__('Manage Locations'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('growdevstorelocations')->__('Location Information'),
            'title'     => Mage::helper('growdevstorelocations')->__('Location Information'),
            'content'   => $this->getLayout()->createBlock('growdev_adminhtml/locations_edit_tab_form')->toHtml(),
        ));

		$this->addTab('products', array(
            'label'     => Mage::helper('catalog')->__('Products Carried At This Location'),
            'content'   => $this->getLayout()->createBlock(
                'growdev_adminhtml/locations_edit_tab_product',
                'location.products.grid'
            )->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
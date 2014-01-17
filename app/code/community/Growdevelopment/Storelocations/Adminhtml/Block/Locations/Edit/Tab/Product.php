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
class Growdevelopment_Storelocations_Adminhtml_Block_Locations_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('store_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_store') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            elseif(!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $this->setDefaultFilter(array('in_store'=>1));

		$collection = Mage::getModel('catalog/product')->getCollection()
			            ->addAttributeToSelect('name')
			            ->addAttributeToSelect('sku');
			            
        $this->setCollection($collection);

        $productIds = $this->_getSelectedProducts();
        if (empty($productIds)) {
            $productIds = 0;
        }
       // $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_store', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_store[]',
            'field_name' => 'in_store[]',
            'values'    =>  $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id'
        ));
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80',
            'index'     => 'sku'
        ));
        
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _getSelectedProducts()
    {
		$store_id = $this->getRequest()->getParam('id');

        $products = $this->getRequest()->getPost('selected_products');
        
        
        if (is_null($products)) {
	        $ids = Mage::getModel('storeproduct/storeproduct')->getCollection()
        						->addFieldToFilter('store_id', array('eq'=> $store_id ));
        						
        	if ( 0 < count($ids)){					
	            foreach($ids as $id){
	            	$products[$id->getProductId()] = 0;
	            }
	            return array_keys($products);
	        } else {
	        	return 0; 
	        }

        }
                
        return $products;
        
    }

}


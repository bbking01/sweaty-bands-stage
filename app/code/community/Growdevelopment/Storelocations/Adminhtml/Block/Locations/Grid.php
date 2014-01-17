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
class Growdevelopment_Storelocations_Adminhtml_Block_Locations_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function _construct()
	{
		parent::_construct();
      $this->setId('locationsGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('ASC');
	}

    /**
     * Prepare collection
     * 
     *
     * 
     */
	protected function _prepareCollection()
	{
	  $collection = Mage::getModel('storelocation/storelocation')->getCollection();
	  $this->setCollection($collection);
	  return parent::_prepareCollection();
	}

    /**
     * Prepare Columns
     * 
     *
     * 
     */
	protected function _prepareColumns()
	{
	  $this->addColumn('id', array(
	      'header'    => Mage::helper('growdevstorelocations')->__('ID'),
	      'align'     =>'right',
	      'width'     => '50px',
	      'index'     => 'id',
	  ));
	
	  $this->addColumn('store_name', array(
	      'header'    => Mage::helper('growdevstorelocations')->__('Store Name'),
	      'align'     =>'left',
	      'index'     => 'store_name',
	  ));
	
	  $this->addColumn('street', array(
	      'header'    => Mage::helper('growdevstorelocations')->__('Street Address'),
	      'align'     =>'left',
	      'index'     => 'street',
	  ));
	  $this->addColumn('city', array(
	      'header'    => Mage::helper('growdevstorelocations')->__('City'),
	      'align'     =>'left',
	      'index'     => 'city',
	  ));
	  $this->addColumn('location_country_id', array(
	      'header'    => Mage::helper('growdevstorelocations')->__('Country'),
	      'align'     =>'left',
	      'index'     => 'location_country_id',
	  ));
	  $this->addColumn('postal_code', array(
	      'header'    => Mage::helper('growdevstorelocations')->__('Postal/Zip Code'),
	      'align'     =>'left',
	      'index'     => 'postal_code',
	  ));
	
	  $this->addColumn('store_type', array(
	      'header'    => Mage::helper('growdevstorelocations')->__('Type'),
	      'align'     =>'left',
	      'index'	  =>'store_type',
	      'type'      => 'options',
	      'options'   => array(
	          1 => 'Online',
	          2 => 'Physical',
	          3 => 'Online & Physical',
	      ),
	  ));
	
	  $this->addColumn('status', array(
	      'header'    => Mage::helper('growdevstorelocations')->__('Status'),
	      'align'     => 'left',
	      'width'     => '80px',
	      'index'     => 'status',
	      'type'      => 'options',
	      'options'   => array(
	          1 => 'Enabled',
	          2 => 'Disabled',
	      ),
	  ));
	
	    $this->addColumn('action',
	        array(
	            'header'    =>  Mage::helper('growdevstorelocations')->__('Action'),
	            'width'     => '100',
	            'type'      => 'action',
	            'getter'    => 'getId',
	            'actions'   => array(
	                array(
	                    'caption'   => Mage::helper('growdevstorelocations')->__('Edit'),
	                    'url'       => array('base'=> '*/*/edit'),
	                    'field'     => 'id'
	                )
	            ),
	            'filter'    => false,
	            'sortable'  => false,
	            'index'     => 'stores',
	            'is_system' => true,
	    ));
	
	
	
	  return parent::_prepareColumns();
	}
	
    /**
     * Prepare collection
     * 
     *
     * 
     */
	protected function _prepareMassaction()	
	{
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('store_id');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('growdevstorelocations')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));

        $statuses = array(
	          1 => 'Enabled',
	          2 => 'Disabled',
	      );

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('growdevstorelocations')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('growdevstorelocations')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        return $this;
	
	}	
    /**
     * Get Row Url
     * 
     *
     * 
     */
	public function getRowUrl($row)
	{
	  return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}
<?php

class Magestore_Clipartmanagement_Block_Adminhtml_Clipartcategory_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  { 		
	  parent::__construct();
      $this->setId('clipartcategoryGrid');
      $this->setDefaultSort('clipart_cat_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {  		
  	  $collection = Mage::getModel('clipartmanagement/clipartcategory')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('clipart_cat_id', array(
          'header'    => Mage::helper('clipartmanagement')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'clipart_cat_id',
      ));

      $this->addColumn('category_name', array(
          'header'    => Mage::helper('clipartmanagement')->__('Clipart Category Name'),
          'align'     =>'left',
          'index'     => 'category_name',
      ));

	  $this->addColumn('position', array(
          'header'    => Mage::helper('clipartmanagement')->__('Category Position'),
          'align'     =>'left',
		  'width'     => '80px',
          'index'     => 'position',
      ));
	  
      $this->addColumn('status', array(
          'header'    => Mage::helper('clipartmanagement')->__('Status'),
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
                'header'    =>  Mage::helper('clipartmanagement')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('clipartmanagement')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('clipartmanagement')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('clipartmanagement')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('clipart_cat_id');
        $this->getMassactionBlock()->setFormFieldName('clipartmanagement');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('clipartmanagement')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('clipartmanagement')->__('Deleting this category will delete all the subcategories and clipart(s) that are belongs to this category. Are you sure want to delete this?')
        ));

        $statuses = Mage::getSingleton('clipartmanagement/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('clipartmanagement')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('clipartmanagement')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
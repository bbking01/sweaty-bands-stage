<?php

class Magestore_Fontmanagement_Block_Adminhtml_Fontcategory_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  { 		
	  parent::__construct();
      $this->setId('fontcategoryGrid');
      $this->setDefaultSort('font_cat_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {  		
  	  $collection = Mage::getModel('fontmanagement/fontcategory')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('font_cat_id', array(
          'header'    => Mage::helper('fontmanagement')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'font_cat_id',
      ));

      $this->addColumn('category_name', array(
          'header'    => Mage::helper('fontmanagement')->__('Font Category Name'),
          'align'     =>'left',
          'index'     => 'category_name',
      ));
	  
	  $this->addColumn('position', array(
          'header'    => Mage::helper('fontmanagement')->__('Category Position'),
          'align'     =>'left',
		  'width'     => '80px',
          'index'     => 'position',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('fontmanagement')->__('Status'),
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
                'header'    =>  Mage::helper('fontmanagement')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('fontmanagement')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('fontmanagement')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('fontmanagement')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('font_cat_id');
        $this->getMassactionBlock()->setFormFieldName('fontmanagement');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('fontmanagement')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('fontmanagement')->__('Deleting this category will delete all Font(s) that are belongs to this category. Are you sure want to delete this?')
        ));

        $statuses = Mage::getSingleton('fontmanagement/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('fontmanagement')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('fontmanagement')->__('Status'),
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
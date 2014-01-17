<?php

class Magestore_Printcolormanagement_Block_Adminhtml_Printcolormanagement_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  { 		
	  parent::__construct();
      $this->setId('printcolormanagementGrid');
      $this->setDefaultSort('color_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {  		
  	  $collection = Mage::getModel('printcolormanagement/printcolormanagement')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('color_id', array(
          'header'    => Mage::helper('printcolormanagement')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'color_id',
      ));

      $this->addColumn('color_name', array(
          'header'    => Mage::helper('printcolormanagement')->__('Color Name'),
          'align'     =>'left',
          'index'     => 'color_name',
      ));	  

      $this->addColumn('status', array(
          'header'    => Mage::helper('printcolormanagement')->__('Status'),
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
                'header'    =>  Mage::helper('printcolormanagement')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('printcolormanagement')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('printcolormanagement')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('printcolormanagement')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('color_id');
        $this->getMassactionBlock()->setFormFieldName('printcolormanagement');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('printcolormanagement')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('printcolormanagement')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('printcolormanagement/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('printcolormanagement')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('printcolormanagement')->__('Status'),
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
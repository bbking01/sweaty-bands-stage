<?php

class Mage_Qrange_Block_Adminhtml_Qrange_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('qrangeGrid');
      $this->setDefaultSort('qrange_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('qrange/qrange')->getCollection();
	  $collection->getSelect()->columns(new Zend_Db_Expr("Concat(quantity_range_from,' - ',quantity_range_to) as quantity_range"));
	  
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('qrange_id', array(
          'header'    => Mage::helper('qrange')->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'qrange_id',
      ));

      $this->addColumn('quantity_range', array(
          'header'    => Mage::helper('qrange')->__('Quantity Range'),
          'align'     => 'left',
		  'index'     => 'quantity_range',
		  'filter'	  => false
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('qrange')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      /*$this->addColumn('status', array(
          'header'    => Mage::helper('qrange')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));*/
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('qrange')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('qrange')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('qrange')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('qrange')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('qrange_id');
        $this->getMassactionBlock()->setFormFieldName('qrange');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('qrange')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('qrange')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('qrange/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('qrange')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('qrange')->__('Status'),
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
<?php
class Mage_Qcprice_Block_Adminhtml_Qcprice_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('qcpriceGrid');
      $this->setDefaultSort('qcprice_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('qcprice/qcprice')->getCollection();
	  $collection->getSelect()
		->columns(new Zend_Db_Expr("Concat(quantity_range_from,' - ',quantity_range_to) as quantity_range"))
		->join('qrange','quantity_range_id=qrange_id')
		->join('colors','colors_counter_id=colors_id',array('colors_counter'=>'colors_counter'));
	  
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('qcprice_id', array(
          'header'    => Mage::helper('qcprice')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'qcprice_id',
      ));

      $this->addColumn('quantity_range', array(
          'header'    => Mage::helper('qcprice')->__('Quantity Range'),
          'align'     =>'left',
          'index'     => 'quantity_range',
		  'filter'	  => false,
      ));
	  
	  $this->addColumn('colors_counter', array(
          'header'    => Mage::helper('qcprice')->__('Color Counter'),
          'align'     =>'left',
          'index'     => 'colors_counter',
		  'filter'	  => false,
      ));
	  
	  $this->addColumn('price', array(
          'header'    => Mage::helper('qcprice')->__('Price'),
          'align'     =>'right',
          'index'     => 'price',
		  'filter'	  => false,
		  'renderer'  => new Mage_Qcprice_Block_Adminhtml_Qcprice_Pricesymbol()
      ));
	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('qcprice')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      /*$this->addColumn('status', array(
          'header'    => Mage::helper('qcprice')->__('Status'),
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
                'header'    =>  Mage::helper('qcprice')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('qcprice')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('qcprice')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('qcprice')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('qcprice_id');
        $this->getMassactionBlock()->setFormFieldName('qcprice');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('qcprice')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('qcprice')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('qcprice/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('qcprice')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('qcprice')->__('Status'),
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
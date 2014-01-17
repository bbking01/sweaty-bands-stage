<?php
class Magestore_Fontmanagement_Block_Adminhtml_Addfont_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  { 		
	  parent::__construct();
      $this->setId('addfontGrid');
      $this->setDefaultSort('font_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
		
  }

  protected function _prepareCollection()
  {  		
  	  $collection = Mage::getModel('fontmanagement/addfont')->getCollection();
	  
	  $collection->join('fontmanagement/fontcategory',
				'font_category_id=	font_cat_id',
				array('category_name'),
				null,
				'left');
	  
      $this->setCollection($collection);
	/*	echo '<pre>';
		print_r($collection->getData());
		echo '</pre>';
		exit;
	*/
     return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('font_id', array(
          'header'    => Mage::helper('fontmanagement')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'font_id',
      ));

      $this->addColumn('font_name', array(
          'header'    => Mage::helper('fontmanagement')->__('Font Name'),
          'align'     =>'left',
          'index'     => 'font_name',
      ));
	  $category = Mage::getModel('fontmanagement/fontcategory')->getCollection()->_toOptionHash();
	 
	  $this->addColumn('font_category_id', array(
          'header'    => Mage::helper('clipartmanagement')->__('Font Category Name'),
          'align'     =>'left',
          'index'     => 'font_category_id',
		  'type'  => 'options',
   			'options' => $category
      ));
	   // $this->addColumn('category_name', array(
          // 'header'    => Mage::helper('fontmanagement')->__('Font Category Name'),
          // 'align'     =>'left',
          // 'index'     => 'category_name',
      // ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('fontmanagement')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
		  'filter_index' => 'main_table.status',
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
        $this->setMassactionIdField('font_id');
        $this->getMassactionBlock()->setFormFieldName('fontmanagement');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('fontmanagement')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('fontmanagement')->__('Are you sure?')
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
<?php

class Magestore_Gallery_Block_Admin_Album_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('albumGrid');
      $this->setDefaultSort('album_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
      $this->setTemplate('designidea/grid.phtml');
	   
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('gallery/album')->getCollection();
  		$store = $this->_getStore();
		if ($store->getId()) {
	            $collection->addStoreFilter($store);
		}
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }
	protected function _getStore()
	{
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}
  protected function _prepareColumns()
  {
      $this->addColumn('album_id', array(
          'header'    => Mage::helper('gallery')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'album_id',
      ));
      $this->addColumn('filename', array(
          'header'    => Mage::helper('gallery')->__('Thumbnail'),
          'align'     =>'left',
          'index'     => 'filename',
      	  'width'	  => '100px',
      ));
      $this->addColumn('title', array(
          'header'    => Mage::helper('gallery')->__('Design Idea Category Name'),
          'align'     =>'left',
          'index'     => 'title',
      ));
	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('gallery')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */
	/* $this->addColumn('order', array(
          'header'    => Mage::helper('gallery')->__('Order'),
          'align'     =>'left',
          'index'     => 'order',
	 	  'width'     => '80px',
      ));
      */
      $this->addColumn('status', array(
          'header'    => Mage::helper('gallery')->__('Status'),
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
                'header'    =>  Mage::helper('gallery')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('gallery')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('gallery')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('gallery')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('album_id');
        $this->getMassactionBlock()->setFormFieldName('album');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('gallery')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('gallery')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('gallery/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('gallery')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('gallery')->__('Status'),
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

	public function getThumbnailSize()
	{
			$size = trim(Mage::getStoreConfig('gallery/info/backend_thumbnail_size'),' ');
			$tmp = explode('-',$size);
			if(sizeof($tmp)==2)
				return array('width'=>is_numeric($tmp[0])?$tmp[0]:85,'height'=>is_numeric($tmp[1])?$tmp[1]:65);
			return array('width'=>85,'height'=>65);
	}
}

<?php
/**
 * @copyright   Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */
class Amasty_Perm_Block_Adminhtml_Reports extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ampermGridReport');
        $this->setUseAjax(true);
    }
    
    protected function getNewFields()
    {
        $fields = array(
            'base_grand_total',
            
            'base_discount_amount',
            'base_discount_canceled',
            'base_discount_invoiced',
            'base_discount_refunded',
            
            'base_shipping_amount',
            'base_shipping_canceled',
            'base_shipping_invoiced',
            'base_shipping_refunded',
            
            'base_shipping_tax_amount',
            'base_shipping_tax_refunded',
            
            'base_subtotal',
            'base_subtotal_canceled',
            'base_subtotal_invoiced',
            'base_subtotal_refunded',
            
            'base_tax_amount',
            'base_tax_canceled',
            'base_tax_invoiced',
            'base_tax_refunded', 
        );
        return $fields; 
    }

    protected function _prepareCollection()
    {
        $orders = Mage::getResourceModel('sales/order_grid_collection');
        
        
        $select = $orders->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        
        $fields = array('increment_id', 'created_at', 'billing_name', 'shipping_name', 'store_id', 'status', 'entity_id');
        $map    = array();
        foreach ($fields as $f)
            $map['m_'.$f] = 'main_table.' . $f;         
        $select->from(null, $map);
        
        //$select->from(null, array());
        $fields = array();
        foreach ($this->getNewFields() as $f)
            $fields['o_'.$f] = 'o.' . $f; 
        
        $select->joinInner( array('o'=>$orders->getTable('sales/order')), 
            'o.entity_id=main_table.entity_id', 
            $fields);     
        
        $userId = $this->getRequest()->getParam('user_id', 0);
        $permissionManager = Mage::getModel('amperm/perm');
        $permissionManager->addOrdersRestriction($orders, $userId);

        $this->setCollection($orders);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('m_increment_id', array(
            'header'    => Mage::helper('customer')->__('Order #'),
            'width'     => '100',
            'index'     => 'm_increment_id',
        ));

        $this->addColumn('m_created_at', array(
            'header'    => Mage::helper('customer')->__('Purchase On'),
            'index'     => 'm_created_at',
            'type'      => 'm_datetime',
        ));

        $this->addColumn('m_billing_name', array(
            'header'    => Mage::helper('customer')->__('Bill to Name'),
            'index'     => 'm_billing_name',
        ));

        $this->addColumn('m_shipping_name', array(
            'header'    => Mage::helper('customer')->__('Shipped to Name'),
            'index'     => 'm_shipping_name',
        ));

        foreach ($this->getNewFields() as $f) {       
            $this->addColumn('o_' . $f, array(
                'header'    => Mage::helper('sales')->__(ucwords(str_replace('_',' ', $f))),
                'index'     => 'o_' . $f,
                'type'      => 'currency',
                'currency'  => 'order_currency_code',
            ));
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('customer')->__('Bought From'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view' => true
            ));
        }

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('customer')->__('Action'),
            'index'     => 'm_entity_id',
            'type'      => 'action',
            'filter'    => false,
            'sortable'  => false,
            'actions'   => array(
                array(
                    'caption' => Mage::helper('sales')->__('View Order'),
                    'url'     => array('base'=>'adminhtml/sales_order/view'),
                    'field'   => 'order_id'
                ),
            )
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('salesrule')->__('CSV'));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/reports', array('_current' => true));
    }

    public function getRowUrl($row)
    {
       return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getMEntityId()));
    }

}

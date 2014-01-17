<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Autorelated_Block_Adminhtml_Widget_Blockchooser extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct($arguments = array())
    {
        parent::__construct($arguments);
        $this->setUseAjax(true);
    }

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqId = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('awautorelated_admin/adminhtml_widget/blockchooser', array('uniq_id' => $uniqId));

        $chooser = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')
            ->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId);

        if ($element->getValue()) {
            $block = Mage::getModel('awautorelated/blocks')->load((int)$element->getValue());
            if ($block->getData()) {
                $chooser->setLabel($block->getName());
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var blockTitle = trElement.down("td").next().innerHTML;
                var blockId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                ' . $chooserJsObject . '.setElementValue(blockId);
                ' . $chooserJsObject . '.setElementLabel(blockTitle);
                ' . $chooserJsObject . '.close();
            }
        ';
        return $js;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('awautorelated/blocks')->getCollection();
        $collection->addStatusFilter();
        $collection->getSelect()->reset(Zend_Db_Select::ORDER);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('awautorelated')->__('ID'),
            'align' => 'right',
            'width' => '5',
            'index' => 'id'
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('awautorelated')->__('Block Name'),
            'align' => 'left',
            'index' => 'name'
        ));

        $this->addColumn('type', array(
            'header' => Mage::helper('awautorelated')->__('Type'),
            'align' => 'center',
            'width' => '150px',
            'index' => 'type',
            'type' => 'options',
            'options' => array(
                AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK => AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK_LABEL,
                AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK => AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK_LABEL,
                AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK => AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK_LABEL
            )
        ));

        $this->addColumn('date_from', array(
            'header' => Mage::helper('awautorelated')->__('Date Start'),
            'align' => 'center',
            'index' => 'date_from',
            'type' => 'date',
            'renderer' => 'AW_Autorelated_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('date_to', array(
            'header' => Mage::helper('awautorelated')->__('Date Expire'),
            'align' => 'center',
            'index' => 'date_to',
            'type' => 'date',
            'renderer' => 'AW_Autorelated_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('awautorelated')->__('Status'),
            'align' => 'center',
            'width' => '100px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                AW_Autorelated_Model_Source_Status::ENABLED => AW_Autorelated_Model_Source_Status::ENABLED_LABEL,
                AW_Autorelated_Model_Source_Status::DISABLED => AW_Autorelated_Model_Source_Status::DISABLED_LABEL,
            )
        ));

        if (!Mage::app()->isSingleStoreMode())
            $this->addColumn('store', array(
                'header' => $this->__('Store View'),
                'index' => 'store',
                'sortable' => FALSE,
                'type' => 'store',
                'store_all' => TRUE,
                'store_view' => TRUE,
                'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
                'filter_condition_callback' => array($this, '_filterStoreCondition')
            ));

        $ret = parent::_prepareColumns();

        return $ret;
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!($value = $column->getFilter()->getValue())) return;
        $collection->addStoreFilter($value);
    }

    public function getGridUrl()
    {
        return $this->getUrl('awautorelated_admin/adminhtml_widget/blockchooser', array('_current' => true));
    }
}

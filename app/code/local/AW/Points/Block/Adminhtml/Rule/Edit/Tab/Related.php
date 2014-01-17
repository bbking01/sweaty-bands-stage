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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Adminhtml_Rule_Edit_Tab_Related extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('related_block_grid');
        $this->setDefaultSort('block_id', 'desc');
        $this->setUseAjax(true);
    }

    protected function _getSelectedBlocks() {
        return $this->getRequest()->getPost('selected_blocks', array());
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('cms/block')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('selected_blocks', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_blocks',
            'values' => $this->_getSelectedBlocks(),
            'align' => 'center',
            'index' => 'block_id',
            'width' => '15',
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('customer')->__('Block title'),
            'index' => 'title',
        ));

        $this->addColumn('identifier', array(
            'header' => Mage::helper('customer')->__('Identifier'),
            'width' => '150',
            'index' => 'identifier',
        ));

        $this->addColumn('creation_time', array(
            'header' => Mage::helper('customer')->__('Date created'),
            'width' => '150',
            'index' => 'creation_time',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/relatedGrid', array('_current' => true));
    }

    public function getBlocksJson() {
        if ($blocks = Mage::registry('points_rule_data')->getStaticBlocksIds()) {
            $blocksArray = array();
            foreach (explode(',', $blocks) as $block) {
                $blocksArray[$block] = true;
            }
            return Zend_Json::encode($blocksArray, false, array());
        }
        return '{}';
    }

}

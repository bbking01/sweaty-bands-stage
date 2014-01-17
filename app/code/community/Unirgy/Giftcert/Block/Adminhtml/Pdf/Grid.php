<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Block_Adminhtml_Pdf_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('giftCertPdfGrid');
        $this->setDefaultSort('template_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('cert_pdf_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ugiftcert/pdf_model')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('ugiftcert');

        $this->addColumn('template_id', array(
            'header'            => $hlp->__('Template ID'),
            'align'             => 'right',
            'width'             => '50px',
            'index'             => 'template_id',
            'type'              => 'number',
        ));

        $this->addColumn('title', array(
            'header'    => $hlp->__('Template Title'),
            'align'     => 'left',
            'index'     => 'title',
        ));

        $this->addColumn('added_at', array(
            'header'    => $hlp->__('Added on'),
            'align'     => 'left',
            'index'     => 'added_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('modified_at', array(
            'header'    => $hlp->__('Modified on'),
            'align'     => 'left',
            'index'     => 'modified_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('added_by', array(
            'header'    => $hlp->__('Created by'),
            'align'     => 'left',
            'index'     => 'added_by',
        ));

        $this->addColumn('modified_by', array(
            'header'    => $hlp->__('Last modified by'),
            'align'     => 'left',
            'index'     => 'modified_by',
        ));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('template_id');
        $block = $this->getMassactionBlock();

        $block->setFormFieldName('ids');

        $block->addItem('delete', array(
             'label'=> Mage::helper('ugiftcert')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('ugiftcert')->__('Are you sure?')
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}

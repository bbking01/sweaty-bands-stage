<?php
/**
 * Unirgy_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_StoreLocator
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_StoreLocator
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_StoreLocator_Block_Adminhtml_Location_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('locationsGrid');
        $this->setDefaultSort('location_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $this->setCollection(Mage::getModel('ustorelocator/location')->getCollection());

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('location_id', array(
                                             'header' => Mage::helper('ustorelocator')->__('ID'),
                                             'align'  => 'right',
                                             'width'  => '50px',
                                             'index'  => 'location_id',
                                             'type'   => 'number',
                                        ));

        $this->addColumn('title', array(
                                       'header' => Mage::helper('ustorelocator')->__('Title'),
                                       'align'  => 'left',
                                       'index'  => 'title',
                                  ));

        $this->addColumn('address', array(
                                         'header' => Mage::helper('ustorelocator')->__('Address'),
                                         'align'  => 'left',
                                         'index'  => 'address',
                                    ));

        $this->addColumn('website_url', array(
                                             'header' => Mage::helper('ustorelocator')->__('URL'),
                                             'index'  => 'website_url',
                                        ));

        $this->addColumn('phone', array(
                                       'header' => Mage::helper('ustorelocator')->__('Phone'),
                                       'index'  => 'phone',
                                  ));

        $this->addColumn('longitude', array(
                                           'header' => Mage::helper('ustorelocator')->__('Longitude'),
                                           'align'  => 'right',
                                           'index'  => 'longitude',
                                           'width'  => '50px',
                                           'type'   => 'number',
                                      ));

        $this->addColumn('latitude', array(
                                          'header' => Mage::helper('ustorelocator')->__('Latitude'),
                                          'align'  => 'right',
                                          'index'  => 'latitude',
                                          'width'  => '50px',
                                          'type'   => 'number',
                                     ));

        Mage::dispatchEvent('ustorelocator_adminhtml_grid_prepare_columns', array('block' => $this));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('location_id');
        $block = $this->getMassactionBlock();
        $block->setFormFieldName('location');

        $block->addItem('delete', array(
                                       'label'   => Mage::helper('ustorelocator')->__('Delete'),
                                       'url'     => $this->getUrl('*/*/massDelete'),
                                       'confirm' => Mage::helper('ustorelocator')->__('Are you sure?')
                                  ));
        $block->addItem('coordinates', array(
                                            'label'    => Mage::helper('ustorelocator')->__('Update coordinates'),
                                            'url'      => $this->getUrl('*/*/massCoordinates'),
//                                            'confirm'  => Mage::helper('ustorelocator')->__('Are you sure?'),
                                            'selected' => 'selected'
                                       ));

        return $this;
    }

}

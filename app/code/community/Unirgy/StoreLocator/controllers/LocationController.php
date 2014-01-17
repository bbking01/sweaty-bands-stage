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
class Unirgy_StoreLocator_LocationController extends Mage_Core_Controller_Front_Action
{

    public function mapAction()
    {
        $isAjax = $this->getRequest()->getParam('ajax', false);
        if ($isAjax) { // if page parameter is passed, then this is called from pager links
            return $this->mapAjaxAction();
        }
        $this->loadLayout();
        if (method_exists($this, '_title')) {
            $this->_title(Mage::getStoreConfig('ustorelocator/general/page_title'));
        }
        $pageLayout = Mage::getStoreConfig('ustorelocator/general/sl_layout');
        if($pageLayout){
            $this->getLayout()->helper('page/layout')
                            ->applyTemplate($pageLayout);
        }
        $this->renderLayout();
    }

    public function searchAction()
    {
        $dom        = new DOMDocument("1.0");
        $node       = $dom->createElement("markers");
        $parentNode = $dom->appendChild($node);
        try {
            $request       = $this->_initRequest();
            $num           = $request['num'];
            $page          = $request['page'];
            $units         = $request['units'];
            $privateFields = $request['privateFields'];
            /* @var $collection Unirgy_StoreLocator_Model_Mysql4_Location_Collection */
            $collection = Mage::helper('ustorelocator/protected')->getCollection($request);
            $collection->setPageSize($num)->setCurPage($page);

            $i = 0;
            foreach ($collection as $loc) {
//                if ($i == $num) {
//                    break; // reached set limit
//                }
                $node    = $dom->createElement("marker");
                $newNode = $parentNode->appendChild($node);
                $newNode->setAttribute("units", $units);
                $newNode->setAttribute("marker_label", ++$i);
                if (Mage::getStoreConfig('ustorelocator/general/use_geo_address')
                    && !$loc->getData('address_display')
                ) {
                    // add display address after geolocation address
                    $loc->setData('address_display', $loc->getData('address'));
                }
                foreach ($loc->getData() as $k => $v) {
                    if (!$privateFields->$k) {
                        if ($k == 'icon' && !empty($v)) {
                            $v = ltrim($v, '/');
                            if ($icon_info = @getimagesize(Mage::getBaseDir('media') . DS . $v)) {
                                $newNode->setAttribute('icon_width', $icon_info[0]);
                                $newNode->setAttribute('icon_height', $icon_info[1]);
                            }
                            $v = Mage::getBaseUrl('media') . $v;
                        } elseif ($k == 'is_featured') {
                            $v = (boolean)$v;
                        }
                        $newNode->setAttribute($k, $v);
                    }
                }
            }
        } catch (Exception $e) {
            $node = $dom->createElement('error', $e->getMessage());
            $parentNode->appendChild($node);
        }

        $this->getResponse()->setHeader('Content-Type', 'text/xml', true)->setBody($dom->saveXml());
    }

    protected function _initRequest()
    {
        $req           = $this->getRequest();
        $num           = (int)Mage::getStoreConfig('ustorelocator/general/num_results');
        $page          = $req->getParam(Unirgy_StoreLocator_Block_Map::PAGE_VAR, 1);
        $units         = $req->getParam('units', Mage::getStoreConfig('ustorelocator/general/distance_units'));
        $privateFields = Mage::getConfig()->getNode('global/ustorelocator/private_fields');
        $params        = $req->getParams();

        return array_merge(array(
                                'units'         => $units,
                                'num'           => $num,
                                'page'          => $page,
                                'privateFields' => $privateFields
                           ), $params);
    }

    public function searchJsonAction()
    {
        $data = array("markers" => array());
        try {
            $request       = $this->_initRequest();
            $num           = $request['num'];
            $page          = $request['page'];
            $units         = $request['units'];
            $privateFields = $request['privateFields'];

            /* @var $collection Unirgy_StoreLocator_Model_Mysql4_Location_Collection */
            $collection = Mage::helper('ustorelocator/protected')->getCollection($request);

//            $collection->setPageSize($num)->setCurPage($page);
            /* @var $pager Mage_Page_Block_Html_Pager */
            $pager = $this->getLayout()->createBlock('page/html_pager');
            if ($pager instanceof Varien_Object) {
                $pager->setLimit($num)
                    ->setLimitVarName(Unirgy_StoreLocator_Block_Map::LIMIT_VAR)
                    ->setPageVarName(Unirgy_StoreLocator_Block_Map::PAGE_VAR)
                    ->setShowPerPage(false)
                    ->setFrameLength(3)
                    ->setJump(3)
                    ->setCollection($collection)
                    ->setData('show_amounts', false)
                    ->setData('use_container', false);

                $data['pager_html'] = $pager->toHtml();
            }

            $i = 0;
            foreach ($collection as $loc) {
                $newNode['units']        = $units;
                $newNode['marker_label'] = ++$i;
                if (Mage::getStoreConfig('ustorelocator/general/use_geo_address')
                    && !$loc->getData('address_display')
                ) {
                    // add display address after geo location address
                    $loc->setData('address_display', $loc->getData('address'));
                }
                foreach ($loc->getData() as $k => $v) {
                    if (!$privateFields->$k) {
                        if ($k == 'icon' && !empty($v)) {
                            $v = ltrim($v, '/');
                            if ($icon_info = @getimagesize(Mage::getBaseDir('media') . DS . $v)) {
                                $newNode['icon_width']  = (int)$icon_info[0];
                                $newNode['icon_height'] = $icon_info[1];
                            }
                            $v = Mage::getBaseUrl('media') . $v;
                        } elseif ($k == 'is_featured') {
                            $v = (boolean)$v;
                        }
                        $newNode[$k] = $v;
                    }
                } // end fore each loc get data
                $data['markers'][] = $newNode;
            }
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody(Zend_Json::encode($data));
    }

    public function mapAjaxAction()
    {
        $this->loadLayout(); // need to load layout to have map block
        /* @var $map Unirgy_StoreLocator_Block_Map */
        $map        = $this->getLayout()->getBlock('ustorelocator.map');
        $collection = $map->getSlHelper()->getDefaultCollection();
        $map->setCollection($collection);
        $locations = $map->getSlHelper()->prepareLocationData($collection->getData());
        $pagerHtml = $map->getPagerHtml();
        $data      = array(
            'markers'    => $locations,
            'pager_html' => $pagerHtml
        );
        $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody(Zend_Json::encode($data));
        return $this;
    }
}

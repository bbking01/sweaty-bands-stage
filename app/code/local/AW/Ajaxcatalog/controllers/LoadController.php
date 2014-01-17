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
 * @package    AW_Ajaxcatalog
 * @version    1.0.5
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Ajaxcatalog_LoadController extends Mage_Core_Controller_Front_Action
{
    /**
     * Response for Ajax Request
     * @param array $result
     */
    protected function _ajaxResponse($result = array())
    {
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Add price renderer type of product
     *
     * @param Mage_Core_Block_Abstract $block
     * @return AW_Ajaxcatalog_LoadController
     */
    protected function _addPriceBlock(Mage_Core_Block_Abstract $block = null)
    {
        if (!$block) {
            return $this;
        }

        $block->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
        $block->addPriceBlockType('giftcard', 'enterprise_giftcard/catalog_product_price', 'giftcard/catalog/product/price.phtml');
        return $this;
    }


    /**
     * Retrives product list content
     * @return Varien_Object
     */
    protected function _getListContent()
    {
        if ($route = $this->getRequest()->getParam('route')) {
            switch ($route) {
                case 'catalog':
                    { # Catalog
                    #-----------------------------------------------------
                    if ($id = $this->getRequest()->getParam('id')) {

                        $layer = Mage::getSingleton('catalog/layer');
                        $layer->setCurrentCategory($id);

                        $view = Mage::app()->getLayout()->createBlock('catalog/layer_view');
                        /** @var Mage_Catalog_Block_Layer_View */
                        if ($view) {
                            $view->toHtml();
                        }

                        $list = Mage::app()->getLayout()->createBlock('awajaxcatalog/catalog_product_list');

                        if ($list) {
                            $this->_addPriceBlock($list);
                            $list->setNativeTemplate();

                            return new Varien_Object(array(
                                'content' => $list->toHtml(),
                                'next_page' => ($list->needAjaxLoad() ? $list->getNextPageNum() : 0),
                            ));
                        }
                    }

                    break;
                    }

                case 'catalogsearch':
                    { # Catalog Search
                    #-----------------------------------------------------
                    if ($q = $this->getRequest()->getParam('q')) {


                        $layer = Mage::getSingleton('catalog/layer');
                        $view = Mage::app()->getLayout()->createBlock('catalogsearch/layer');
                        if ($view) {
                            $view->toHtml();
                        }

                        $search = Mage::app()->getLayout()->createBlock('awajaxcatalog/catalog_product_search');
                        /** @var Mage_CatalogSearch_Block_Result */

                        $list = Mage::app()->getLayout()->createBlock('awajaxcatalog/catalog_product_list');
                        $search->setChild('search_result_list', $list);
                        $search->setListOrders();
                        $search->setListModes();
                        $search->setListCollection();

                        if ($list) {
                            $this->_addPriceBlock($list);
                            $list->setNativeTemplate();
                            return new Varien_Object(array(
                                'content' => $list->toHtml(),
                                'next_page' => ($list->needAjaxLoad() ? $list->getNextPageNum() : 0),
                            ));
                        }

                    } else {
                        # Is Advanced Search

                        Mage::getSingleton('catalogsearch/advanced')->addFilters($this->getRequest()->getParams());
                        $layer = Mage::getSingleton('catalog/layer');


                        /** @var AW_Ajaxcatalog_Block_Catalog_Product_Advancedsearch */
                        $search = Mage::app()->getLayout()->createBlock('awajaxcatalog/catalog_product_advancedsearch');


                        $list = Mage::app()->getLayout()->createBlock('awajaxcatalog/catalog_product_list');
                        $search->setChild('search_result_list', $list);
                        $search->setListOrders();
                        $search->setListModes();
                        $search->setListCollection();

                        if ($list) {
                            $this->_addPriceBlock($list);
                            $list->setNativeTemplate();
                            return new Varien_Object(array(
                                'content' => $list->toHtml(),
                                'next_page' => ($list->needAjaxLoad() ? $list->getNextPageNum() : 0),
                            ));
                        }
                    }
                    break;
                    }
                case 'tag':
                    { # Search by tag
                    #-----------------------------------------------------
                    if ($tagId = $this->getRequest()->getParam('tagId')) {

                        $tag = Mage::getModel('tag/tag')->load($tagId);

                        if (!$tag->getId() || !$tag->isAvailableInStore()) {
                            $this->_forward('404');
                            return;
                        }
                        Mage::register('current_tag', $tag);
                        $layer = Mage::getSingleton('catalog/layer');
                        $tagResult = Mage::app()->getLayout()->createBlock('awajaxcatalog/catalog_product_tags');

                        $list = Mage::app()->getLayout()->createBlock('awajaxcatalog/catalog_product_list');
                        $tagResult->setChild('search_result_list', $list);
                        $tagResult->setListOrders();
                        $tagResult->setListModes();
                        $tagResult->setListCollection();

                        if ($list) {
                            $list->setNativeTemplate();
                            return new Varien_Object(array(
                                'content' => $list->toHtml(),
                                'next_page' => ($list->needAjaxLoad() ? $list->getNextPageNum() : 0),
                            ));
                        }
                    }
                    break;
                    }
                case 'awadvancedsearch':
                    {
                    if ($q = $this->getRequest()->getParam('q')) {

                        $resultsHelper = Mage::helper('awadvancedsearch/results');
                        $results = $resultsHelper->query($q);
                        if ($results) {
                            $helper = Mage::helper('awadvancedsearch/catalogsearch');
                            $helper->addCatalogsearchQueryResults($q, $results);
                            $helper->setResults($results);
                        }
                        $view = Mage::app()->getLayout()->createBlock('awadvancedsearch/layer')->setTemplate('catalog/layer/view.phtml');
                        if ($view) {
                            $view->toHtml();
                        }
                        $search = Mage::app()->getLayout()->createBlock('awajaxcatalog/catalog_product_awadvancedsearch')->setTemplate('aw_advancedsearch/result.phtml');
                        $list = Mage::app()->getLayout()->createBlock('awajaxcatalog/catalog_product_list');
                        $search->setChild('search_result_catalog', $list);
                        $search->setListOrders();
                        $search->setListModes();
                        $search->setListCollection();
                        if ($list) {
                            $this->_addPriceBlock($list);
                            $list->setNativeTemplate();
                            return new Varien_Object(array(
                                'content' => $list->toHtml(),
                                'next_page' => ($list->needAjaxLoad() ? $list->getNextPageNum() : 0),
                            ));
                        }
                    }
                    break;
                    }
                # etc
            }
        }
    }

    public function nextAction()
    {
        $result = array();
        $content = "";
        Mage::register(AW_Ajaxcatalog_Helper_Data::IS_AJAX_KEY, true, true);
        try {
            if ($pa = $this->getRequest()->getParam('pa')) {
                $params = Zend_Json_Decoder::decode(base64_decode($pa));
                $this->getRequest()->setParams($params);

                $list = $this->_getListContent();
                if ($list) {
                    $result['success'] = true;
                    $result['content'] = $list->getContent();
                    $result['next_page'] = $list->getNextPage();
                }
            }
        } catch (Exception $e) {
            $result['success'] = false;
            $result['error'] = $e->getMessage();
        }

        $this->_ajaxResponse($result);
    }
}
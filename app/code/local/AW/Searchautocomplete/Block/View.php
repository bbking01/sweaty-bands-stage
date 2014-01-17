<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Searchautocomplete
 * @version    3.2.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AW_Searchautocomplete_Block_View extends Mage_Catalog_Block_Product_List
{

    const TEXTAREA_CODE = 'textarea';
    const SEARCHABLE_STATUS = '1';

    protected $_collection = null;

    public static function findNearest($haystack, $needles, $offset)
    {
        $haystackL = strtolower($haystack);
        $nearestWord = '';
        $nearestPos = 999999;
        foreach ($needles as $needle)
            if ($needle
                && false !== ($pos = strpos($haystackL, strtolower($needle), $offset))
                && $nearestPos > $pos
            ) {
                $nearestPos = $pos;
                $nearestWord = substr($haystack, $pos, strlen($needle));
            }
        if ($nearestWord) return array('pos' => $nearestPos, 'word' => $nearestWord);
        else return false;
    }

    public static function decorateWords($words, $subject, $before, $after)
    {
        $replace = array();
        for ($pos = 0; $pos < strlen($subject) && (false !== $nearest = self::findNearest($subject, $words, $pos));)
        {
            $replace[$nearest['pos']] = $nearest['word'];
            $pos = $nearest['pos'] + strlen($nearest['word']);
        }

        $res = '';
        $pos = 0;
        foreach ($replace as $start => $word)
        {
            $res .= substr($subject, $pos, $start - $pos) . $before . $word . $after;
            $pos = $start + strlen($word);
        }
        $res .= substr($subject, $pos);

        return $res;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('searchautocomplete/product');
        $collection->addAttributeToSelect('*');
        return $collection;
    }

    protected function _postProcessCollection()
    {
        $this->_collection->addUrlRewrites()
            ->addMinimalPrice()
            ->addFinalPrice()
            ->groupByAttribute('entity_id');
        return $this;
    }

    public function getItems()
    {
        $_res = array();

        $request = Mage::app()->getRequest();

        $qParam = $request->getParam('q');
        $storeId = Mage::app()->getStore()->getId();

        if (is_null($qParam) || !$storeId) return;

        $q = Mage::helper('core')->htmlEscape($qParam);
        $q = htmlspecialchars_decode($q);
        $template = Mage::getStoreConfig('searchautocomplete/interface/item_template');
        //check is advsearch is installed and enabled
        $allAttributes = AW_Searchautocomplete_Model_Source_Product_Attribute::getProductAttributeList();
        // deciding which attributes are used in the template
        $usedAttributes = array();
        foreach ($allAttributes as $id => $attrData)
            if (false !== strpos($template, '{' . $attrData['code'] . '}'))
                $usedAttributes[] = $attrData['code'];
        $searchedWords = explode(' ', trim($q));

        for ($i = 0; $i < count($searchedWords); $i++) {
            if (strlen($searchedWords[$i]) < 2 || preg_match('(:)', $searchedWords[$i]))
                unset($searchedWords[$i]);
        }

        if (Mage::helper('searchautocomplete')->canUseADVSearch()) {
            try {
                $this->_collection = Mage::getModel('awadvancedsearch/api')->catalogQuery($q . '*', $storeId);
            } catch (Exception $e) {
            }
        }

        if (!Mage::helper('searchautocomplete')->canUseADVSearch() || is_null($this->_collection) || !$this->_collection) {

            $this->_collection = $this->_prepareCollection();
            $fulltext = false;
            // deciding which attributes are used in the template
            $searchableAttributes = explode(',', Mage::getStoreConfig('searchautocomplete/interface/searchable_attributes'));
            $attributes = array();
            foreach ($searchableAttributes as $attrId) {
                if (array_key_exists($attrId, $allAttributes))
                    $attributes[$attrId] = $allAttributes[$attrId]['type'];
                $aasd = Mage::getModel('eav/entity_attribute')->load($attrId);
                if ($aasd->getData('frontend_input') == self::TEXTAREA_CODE) {
                    if ($aasd->getData('is_searchable') == self::SEARCHABLE_STATUS) {
                        $fulltext = true;
                    }
                }
            }
            $productIds = false;
            try {

                if ($fulltext) {
                    $productIds = AW_Searchautocomplete_Model_Source_Product_Attribute::getProductIds2($q, $storeId);
                } else {
                    $productIds = AW_Searchautocomplete_Model_Source_Product_Attribute::getProductIds($attributes, $searchedWords, $storeId);
                }

            } catch (Exception $e) {
            }
            if (!$productIds) return array();
            if (!count($productIds)) return array();


            $this->_collection->addFilterByIds($productIds);
            $visibility = array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            $this->_collection
                ->addStoreFilter($storeId)
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->setVisibility($visibility);
        }

        $this->_postProcessCollection();

        $this->_collection->setPageSize(Mage::getStoreConfig('searchautocomplete/interface/show_top_x'));

        $thumbnailSize = (int)Mage::getStoreConfig('searchautocomplete/interface/thumbnail_size');
        if (!$thumbnailSize) $thumbnailSize = 75;

        $thumbnailUrlPresents = (false !== strpos($template, '{thumbnail_url}'));
        foreach ($this->_collection as $_product)
        {

            $productUrl = $_product->getProductUrl();
            $this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
            $this->addPriceBlockType('giftcard', 'enterprise_giftcard/catalog_product_price', 'giftcard/catalog/product/price.phtml');
            $this->addPriceBlockType('msrp', 'catalog/product_price', 'catalog/product/price_msrp.phtml');
            $this->addPriceBlockType('msrp_item', 'catalog/product_price', 'catalog/product/price_msrp_item.phtml');
            $this->addPriceBlockType('msrp_noform', 'catalog/product_price', 'catalog/product/price_msrp_noform.phtml');

            $priceHTML = $this->getPriceHtml($_product, true);
            $info = $template;

            foreach ($usedAttributes as $code)
            {
                $data = $_product->getData($code);

                if (!is_string($data)) continue;
                $data = self::decorateWords($searchedWords, $data, '<strong class="searched-words">', '</strong>');
                if ($code == 'price') {
                    //$data = Mage::app()->getStore()->convertPrice($productPrice, true, true);
                    $data = $priceHTML;
                }
                $data = '<div class="std">' . $data . '</div>';
                $info = str_replace('{' . $code . '}', $data, $info);
            }
            //             adding special fields
            $info = str_replace('{product_url}', $productUrl, $info);

            if ($thumbnailUrlPresents)
                $info = str_replace('{thumbnail_url}', $_product->getThumbnailUrl($thumbnailSize, $thumbnailSize), $info);

            array_push($_res, array(
                'content' => str_replace('"', '\'', $info),
                'url' => $productUrl,
            ));
        }

        return $_res;

    }
}
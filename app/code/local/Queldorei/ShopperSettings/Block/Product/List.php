<?php

class Queldorei_ShopperSettings_Block_Product_List extends Mage_Catalog_Block_Product_List
{

    protected function _beforeToHtml()
    {
        $collection = $this->_getProductCollection();
        $numProducts = $this->getNumProducts();
        if ( $numProducts ) {
            $collection->setPageSize($numProducts)->load();
        }
        $this->setCollection($collection);

        return parent::_beforeToHtml();
    }

    public function getBlockTitle()
    {
        $title = $this->getTitle();
        if (empty($title)) {
            $title = 'Featured Products';
        }
        return $title;
    }

}
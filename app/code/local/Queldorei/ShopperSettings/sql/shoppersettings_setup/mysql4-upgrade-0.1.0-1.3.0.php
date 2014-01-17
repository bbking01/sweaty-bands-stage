<?php
/**
 * @version   1.0 12.0.2012
 * @author    queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 queldorei
 */


try {
//create left col home page if not exist
    $is_page_exist = Mage::getModel('cms/page')->getCollection()
        ->addFieldToFilter('identifier', 'shopper_home_2col_left')
        ->load();

    if ( !count($is_page_exist) ) {
        $cmsPage = array(
            'title' => 'Shopper Home page - left column',
            'identifier' => 'shopper_home_2col_left',
            'content' => '<div class="home-left-col clearfix">
<div class="home-main">{{block type="shoppersettings/product_list" category_id="12" num_products="6" template="catalog/product/featured_products.phtml"}}</div>
<div class="home-left">{{block type="cms/block" block_id="shopper_banners_slideshow" }} {{block type="newsletter/subscribe" template="newsletter/subscribe_home.phtml" }} {{block type="shoppersettings/bestsellers" template="queldorei/bestsellers.phtml" }}</div>
</div></div>',
            'is_active' => 1,
            'sort_order' => 0,
            'stores' => array(0),
            'root_template' => 'one_column'
        );
        Mage::getModel('cms/page')->setData($cmsPage)->save();
    }

}
catch (Exception $e) {
    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('An error occurred while updating shopper theme pages and cms blocks.'));
}
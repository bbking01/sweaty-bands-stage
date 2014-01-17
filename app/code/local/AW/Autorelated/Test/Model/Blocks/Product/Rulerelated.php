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


class AW_Autorelated_Test_Model_Blocks_Product_Rulerelated extends EcomDev_PHPUnit_Test_Case {
    
    public function testGetMatchingProductIds() {
        
        /*
        //my match
        $rule = Mage::getModel('awautorelated/blocks')->load(3);

        $model = Mage::getModel('awautorelated/blocks_product_rulerelated');
        $model->setWebsiteIds(1);
        $conditions = $rule->getRelatedProducts()->getRelated();
        $model->getConditions()->loadArray($conditions['conditions'],'related');
        
        $start = time();
        $myIds = $model->getMatchingProductIds();
        $finish = time();
        
        var_dump($myIds);
        $myTime = $finish - $start;

        
        //native
        
        $rule = Mage::getModel('awautorelated/blocks')->load(3);

        $nativeModel = Mage::getModel('catalogrule/rule');
        $nativeModel->setWebsiteIds(1);
        $conditions = $rule->getRelatedProducts()->getRelated();
        $nativeModel->getConditions()->loadArray($conditions['conditions'],'related');

        $start = time();
        $ids = $nativeModel->getMatchingProductIds();
        $finish = time();
        
        $time = $finish - $start;
        
        var_dump($ids);
        
        $diff = array_diff($ids,$myIds);
        var_dump('diff = ');
        var_dump($diff);
        var_dump('my engine time = ' . $myTime);
        var_dump('magento engine time = ' . $time);
        
        $this->assertTrue($myIds == $ids);
        //$this->assertEquals($myIds, $ids);

         */        
        
    }
    
}

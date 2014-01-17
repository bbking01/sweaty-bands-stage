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


class AW_Autorelated_Test_Model_Blocks extends EcomDev_PHPUnit_Test_Case {
    /**
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function testSavenLoad($customGroups, $currentlyViewed, $relatedProducts) {
        $model = Mage::getModel('awautorelated/blocks');
        $model->setData(array(
            'name' => 1,
            'status' => 1,
            'customer_groups' => $customGroups,
            'priority' => 1,
            'date_from' => 'NOW()',
            'date_to' => 'NOW()',
            'position' => 1,
            'type' => 1,
            'currently_viewed' => $currentlyViewed,
            'related_products' => $relatedProducts
        ));
        $model->save();
        $newId = $model->getId();
        unset($model);

        if(!is_array($customGroups)) $customGroups = @explode(',', (string)$customGroups);
        
        $model = Mage::getModel('awautorelated/blocks')->load($newId);
        $this->assertEquals(
            $model->getData('customer_groups'), $customGroups
        );
    }
}

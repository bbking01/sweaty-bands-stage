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


class AW_Autorelated_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case {

    /**
     * Check version test - test cases are written for Magento 1.5.x
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function testCheckVersion($version) {
        $helper = Mage::helper('awautorelated');
        $this->assertEquals(
                $this->expected('ver' . $version)->getRes(), $helper->checkVersion($version)
        );
    }

    /**
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function testPrepareArray($pass, $arr) {
        $helper = Mage::helper('awautorelated');
        $this->assertEquals(
                $this->expected('pass' . $pass)->getArr(), $helper->prepareArray($arr)
        );
    }

    public function testGetAbstractBlock() {
        $helper = Mage::helper('awautorelated');
        $aBlock = $helper->getAbstractProductBlock();
        $this->assertEquals(
                get_class($aBlock), 'Mage_Catalog_Block_Product_List'
        );

        $aBlock->setData('test', '123');
        $aBlock = $helper->getAbstractProductBlock();
        $this->assertEquals(
                $aBlock->getData('test'), '123'
        );
    }

}

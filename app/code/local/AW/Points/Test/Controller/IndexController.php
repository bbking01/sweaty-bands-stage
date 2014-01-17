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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 *
 */
class AW_Points_Test_Controller_IndexController extends EcomDev_PHPUnit_Test_Case_Controller {

    /**
     * infopageAction test
     *
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function infopageAction($testId, $pageId, $return) {
        $this->registerPointsConfigMockObject($pageId);
        $expected = $this->expected('id' . $testId);
        if (!$return)
            $this->registerCmsPageMockObject($return);
        $this->reset()->dispatch('points/index/infopage');
        $this->assertRequestRoute($expected->getRequestRoute());
        $this->assertResponseBodyContains($expected->getBodyContains());
    }

    /**
     * Register mock object for helper points/config
     */
    protected function registerPointsConfigMockObject($pageId) {
        $stub = $this->getHelperMock('points/config', array('getInfoPageId'));
        $stub->expects($this->any())
                ->method('getInfoPageId')
                ->will($this->returnValue($pageId));
        $this->replaceByMock('helper', 'points/config', $stub);
    }

    /**
     * Register mock object for CMS Page
     */
    protected function registerCmsPageMockObject($return) {
        $stub = $this->getHelperMock('cms/page', array('renderPage'));
        $stub->expects($this->any())
                ->method('renderPage')
                ->will($this->returnValue($return));
        $this->replaceByMock('helper', 'cms/page', $stub);
    }

}
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
class AW_Points_Test_Controller_RewardController extends EcomDev_PHPUnit_Test_Case_Controller {

    /**
     * indexAction test
     *
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function indexAction($testId, $enabled, $isLoggedIn, $subscribe) {
        $this->registerPointsConfigMockObject($enabled);
        $this->registerCustomerSessionMockObject($isLoggedIn);
        $expected = $this->expected('id' . $testId);

        if ($subscribe)
            $this->getRequest()->setQuery('subscribe', true);
        $this->dispatch('points/reward/index');
        if ($expected->getIsSubscribe()) {
            $this->assertRedirect();
        } elseif (!$expected->getEnabled()) {
            $this->assertResponseHeaderNotContains('Location', 'login');
        } else {
            if (!$expected->getIsLoggedIn()) {
                $this->assertResponseHeaderContains('Location', 'login');
            } else {
                $this->assertResponseBodyContains("<title>Reward Points</title>");
            }
        }
    }

    /**
     * subscribeAction test
     *
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function subscribeAction($testId, $subscribe) {
        $this->registerPointsConfigMockObject(1);
        $this->registerCustomerSessionMockObject(1);
        $expected = $this->expected('id' . $testId);

        if ($subscribe)
            $this->getRequest()->setQuery('is_subscribed', true);
        $this->dispatch('points/reward/subscribe');
        $this->assertRedirect();
        $items = Mage::getSingleton('customer/session')->getMessages(true)->getItems(null);
        $this->assertEquals(
                $expected->getMessageCode(), $items[0]->getCode()
        );
    }

    /**
     * Register mock object for helper points/config
     */
    protected function registerPointsConfigMockObject($enabled) {
        $stub = $this->getHelperMock('points/config', array('isPointsEnabled'));
        $stub->expects($this->any())
                ->method('isPointsEnabled')
                ->will($this->returnValue($enabled));
        $this->replaceByMock('helper', 'points/config', $stub);
    }

    /**
     *
     */
    protected function registerCustomerSessionMockObject($isLoggedIn) {
        $stub = $this->getModelMock('customer/session', array('isLoggedIn'));
        $stub->expects($this->any())
                ->method('isLoggedIn')
                ->will($this->returnValue($isLoggedIn));
        $this->replaceByMock('singleton', 'customer/session', $stub);
    }

}
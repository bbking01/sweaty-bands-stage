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
class AW_Points_Test_Controller_InvitationController extends EcomDev_PHPUnit_Test_Case_Controller {

    /**
     * indexAction test
     *
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function indexAction($testId, $enabled, $isLoggedIn) {
        $this->registerPointsConfigMockObject($enabled);
        $this->registerCustomerSessionMockObject($isLoggedIn);
        $expected = $this->expected('id' . $testId);

        $this->dispatch('points/invitation/index');
        if (!$expected->getEnabled()) {
            $this->assertResponseHeaderNotContains('Location', 'login');
        } else {
            if (!$expected->getIsLoggedIn()) {
                $this->assertResponseHeaderContains('Location', 'login');
            } else {
                $this->assertResponseBodyContains("<title>My Invitation</title>");
            }
        }
    }

    /**
     * indexAction test
     *
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function createAccountAction($testId) {
        $this->registerPointsConfigMockObject(1);
        $this->registerCustomerSessionMockObject(1);
        $this->dispatch('points/invitation/createAccount');
        $this->assertRedirect();
    }

    /**
     * indexAction test
     *
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function sendInvitationAction($testId, $email, $message, $return, $exception) {
        $this->registerPointsConfigMockObject(1);
        $this->registerCustomerSessionMockObject(1);
        $this->registerPointsInvitationMockObject($return, $exception);
        if ($exception == 20)
            $this->registerCustomerCustomerMockObject($exception);
        $expected = $this->expected('id' . $testId);

        if (!is_null($email)) {
            $post['email'] = array($email);
            if (!is_null($message))
                $post['message'] = $message;
            $this->getRequest()->setPost($post);
        }
        $this->dispatch('points/invitation/sendInvitation');

        $items = Mage::getSingleton('customer/session')->getMessages(true)->getItems(null);
        if (!is_null($expected->getMessage())) {
            $this->assertEquals(
                    $expected->getMessage(), $items[0]->getCode()
            );
        }

        if (is_null($email))
            $this->assertResponseBodyContains("<title>Send Invitations</title>");
        else
            $this->assertRedirect();
    }

    /**
     * Register mock object for helper points/config
     */
    protected function registerPointsConfigMockObject($enabled) {
        $stub = $this->getHelperMock('points/config', array('isPointsEnabled', 'isReferalSystemEnabled'));
        $stub->expects($this->any())
                ->method('isPointsEnabled')
                ->will($this->returnValue($enabled));

        $stub->expects($this->any())
                ->method('isReferalSystemEnabled')
                ->will($this->returnValue($enabled));
        $this->replaceByMock('helper', 'points/config', $stub);
    }

    /**
     *
     */
    protected function registerCustomerSessionMockObject($isLoggedIn) {
        $stub = $this->getModelMock('customer/session', array('isLoggedIn', 'getCustomer'));
        $stub->expects($this->any())
                ->method('isLoggedIn')
                ->will($this->returnValue($isLoggedIn));

        $customer = Mage::getModel('customer/customer')->setId(1)->setEntityId(1);
        $stub->expects($this->any())
                ->method('getCustomer')
                ->will($this->returnValue($customer));
        $this->replaceByMock('singleton', 'customer/session', $stub);
    }

    /**
     *
     */
    protected function registerPointsInvitationMockObject($return, $exception) {
        $stub = $this->getModelMock('points/invitation', array('sendEmail', 'getStatus'));
        $stub->expects($this->any())
                ->method('sendEmail')
                ->will($this->returnValue($return));

        if ($exception == 10) {
            $stub->expects($this->any())
                    ->method('getStatus')
                    ->will($this->returnValue(1000));
        }

        $this->replaceByMock('model', 'points/invitation', $stub);
    }

    /**
     *
     */
    protected function registerCustomerCustomerMockObject($id) {
        $stub = $this->getModelMock('customer/customer', array('getId'));
        $stub->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($id));
        $this->replaceByMock('model', 'customer/customer', $stub);
    }

}
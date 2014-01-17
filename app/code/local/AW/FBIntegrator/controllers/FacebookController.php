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
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_FBIntegrator
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_FBIntegrator_FacebookController extends Mage_Core_Controller_Front_Action {

    public function checkappAction() {
        return $this->getResponse()->setBody(Mage::helper('fbintegrator')->checkApp($this->getRequest()->getParam('app_id'), $this->getRequest()->getParam('app_secret')));
    }

    public function connectAction() {

        $helper = Mage::helper('fbintegrator');
        $facebook = Mage::getModel('fbintegrator/facebook_api', $helper->getAppConfig());

        $session = Mage::getSingleton('customer/session');

        $request = $this->getRequest();
        $accessToken = $request->getParam('accessToken');

        if (!$accessToken) {
            $accessToken = $session->getFBIAccessToken();
        }
        if ($accessToken) {
            $session->setFBIAccessToken($accessToken);
            $facebook->setAccessToken($accessToken);
        }

        $me = null;
        try {
            $me = $facebook->api('/me');
        } catch (Exception $exc) {
            
        }

        if ($me && isset($me['email'])) {

            $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

            $fbUser = Mage::getModel('fbintegrator/users')->getUser($me['email']);

            if ($fbUser->getCustomerId()) {

                $customer->load($fbUser->getCustomerId());
            } else {

                $customer->loadByEmail($me['email']);

                if ($customer->getId()) {
                    /* store customer exists, add new FBI customer */
                    Mage::getModel('fbintegrator/users')->createUser($me['id'], $customer->getEmail(), $customer->getId());
                } else {
                    /* new store customer, new FBI customer */

                    /* registration form, required fields */
                    if ($helper->getCountRequiredFields() && !$request->getParam('from-required-form')) {
                        return $this->getResponse()->setRedirect($helper->getRequiredFormUrl());
                    }

                    $data = array(
                        'firstname' => $me['first_name'],
                        'lastname' => $me['last_name'],
                        'email' => $me['email'],
                        'gender' => $helper->getUserGender($me),
                        'dob' => date('Y-m-d', strtotime(isset($me['birthday']) ? $me['birthday'] : '0000-00-00')),
                        'prefix' => $request->getParam('prefix'),
                        'suffix' => $request->getParam('suffix'),
                        'taxvat' => $request->getParam('taxvat'),
                    );

                    try {
                        $customer = $helper->registerCustomer($data);
                        if ($customer->getId()) {
                            Mage::getModel('fbintegrator/users')->createUser($me['id'], $me['email'], $customer->getId());
                            $customer->sendNewAccountEmail('registered');
                            $session->addSuccess($helper->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName()));
                        }
                    } catch (Exception $exc) {
                        $session->addError($helper->__('Error during registering with %s.', Mage::app()->getStore()->getFrontendName()));
                    }
                }
            }

            if ($customer->getId()) {
                $session->setCustomerAsLoggedIn($customer);
                if (method_exists($session, 'renewSession')) {
                    $session->renewSession();
                }
            }
        } else {
            $session->addError($helper->__('Unable to log in with Facebook. Please, try again later.'));
        }
        $this->_loginPostRedirect();
    }

    public function formAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Create New Customer Account'));
        $this->renderLayout();
    }

    public function likeAction() {

        $response = new Varien_Object();
        $response->setError(0);

        if (Mage::helper('fbintegrator/like')->canGetPoints()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customer->getId();
            $url = $this->getRequest()->getParam('url');
            $pointsForLike = Mage::helper('fbintegrator/like')->getPoints();

            $code = $this->getRequest()->getParam('code');
            if (Mage::helper('fbintegrator')->getUrlSecretCode($url) !== $code) {
                $response->setError(1);
                $this->getResponse()->setBody($response->toJson());
                return;
            }

            switch ($this->getRequest()->getParam('action')) {
                case 'like':

                    $validator = Mage::getModel('fbintegrator/like_validator');

                    /*  already liked  */
                    if ($validator->isAlreadyLiked($customerId, $url)) {
                        $response->setError(1);
                        $response->setMessage($this->__('Already liked'));
                        $this->getResponse()->setBody($response->toJson());
                        return;
                    }

                    /*    delay between retries     */
                    $timeToWait = $validator->getTimeToWait($customerId);
                    if ($timeToWait) {
                        $response->setError(1);
                        $response->setMessage($this->__("Please wait %s seconds", $timeToWait));
                        $this->getResponse()->setBody($response->toJson());
                        return;
                    }

                    /*   N likes per M seconds  */
                    if ($validator->limitLikesPerTimeReached($customerId)) {
                        $response->setError(1);
                        $response->setMessage($this->__("You've already reached the \"like\" limit"));
                        $this->getResponse()->setBody($response->toJson());
                        return;
                    }

                    /* save new like */
                    try {
                        $like = Mage::getModel('fbintegrator/like');
                        $like->setCustomerId($customerId)
                                ->setLikeTime(Mage::getModel('core/date')->gmtTimestamp())
                                ->setUrl($this->getRequest()->getParam('url'))
                                ->save();

                        $pointsApi = Mage::getModel('points/api');
                        $transaction = $pointsApi->addTransaction($pointsForLike, 'added_by_admin', $customer, null, array(
                            'comment' => $this->__("Facebook Like")
                                )
                        );
                        if (!$transaction) {
                            $response->setError(1);
                        } else {
                            $response->setMessage($this->__("You've just got <b>%s</b> point(s)", $pointsForLike));
                        }
                    } catch (Exception $exc) {
                        $response->setError(1);
                    }

                    break;

                case 'unlike':
                    $like = Mage::getModel('fbintegrator/like')
                            ->getStoredLike($customerId, $url);
                    if ($like->getId()) {
                        try {
                            $like->delete();

                            $pointsApi = Mage::getModel('points/api');
                            $transaction = $pointsApi->addTransaction(
                                    -$pointsForLike, 'added_by_admin', $customer, null, array(
                                'comment' => $this->__("Facebook Unlike")
                                    )
                            );
                            if (!$transaction) {
                                $response->setError(1);
                            } else {
                                $response->setMessage($this->__("Like and get <b>%s</b> point(s)", $pointsForLike));
                            }
                        } catch (Exception $exc) {
                            $response->setError(1);
                        }
                    }

                    break;

                default:
                    $response->setError(1);
                    break;
            }
        } else {
            $response->setError(1);
        }
        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Define target URL and redirect customer after logging in
     */
    protected function _loginPostRedirect() {
        $session = Mage::getSingleton('customer/session');

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag(
                                //Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                                'customer/startup/redirect_dashboard'
                )) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        // Rebuild referer URL to handle the case when SID was changed
                        $referer = Mage::getModel('core/url')
                                ->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }

}

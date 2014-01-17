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


class AW_FBIntegrator_Helper_Data extends Mage_Core_Helper_Abstract {
    const SECRET_CODE_SALT="g6Unf0";
    /*
     * Compare param $version with magento version
     */

    public function checkVersion($version) {
        return version_compare(Mage::getVersion(), $version, '>=');
    }

    # fix for wall stream doubling

    public function registerOrder($orderId) {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $orders = $session->getFaceBookPublishedOrders();

        if (count($orders)) {
            $orders[] = $orderId;
        } else {
            $orders = array($orderId);
        }
        $session->setFaceBookPublishedOrders($orders);
        return $this;
    }

    public function isRegisteredOrder($orderId) {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $orders = $session->getFaceBookPublishedOrders();
        if (isset($orders)) {
            return in_array($orderId, $orders);
        } else {
            return false;
        }
    }

    # end fix

    public function extEnabled() {
        return Mage::getStoreConfigFlag('fbintegrator/general/enabled');
    }

    public function getAppKey() {
        return Mage::getStoreConfig('fbintegrator/app/api_key');
    }

    public function getAppSecret() {
        return Mage::getStoreConfig('fbintegrator/app/secret');
    }

    public function getWallEnabled() {
        return Mage::getStoreConfigFlag('fbintegrator/wall/enabled');
    }

    public function getWallMessage() {
        return Mage::getStoreConfig('fbintegrator/wall/post_message');
    }

    public function getWallTemplate() {
        return Mage::getStoreConfig('fbintegrator/wall/post_link_template');
    }

    public function getWallWishlistTemplate() {
        return Mage::getStoreConfig('fbintegrator/wall/wishlist_share_message');
    }

    public function getWallCount() {
        return Mage::getStoreConfig('fbintegrator/wall/items_count');
    }

    public function postImagesToWall() {
        return Mage::getStoreConfigFlag('fbintegrator/wall/picture');
    }

    public function getProductRewriteUrl($productId) {
        $collection = Mage::getModel('core/url_rewrite')->getCollection();
        $collection->getSelect()
                ->where('product_id = ?', $productId)
                ->where('store_id = ?', Mage::app()->getStore()->getId())
        ;

        if (count($collection)) {
            $path = $collection->getColumnValues('request_path');
            return reset($path);
        }
        else
            return 'catalog/product/view/id/' . $productId;
    }

    public function isSecure() {
        return Mage::getStoreConfig('web/secure/use_in_frontend', Mage::app()->getStore()->getId());
    }

    public function addCode() {
        return Mage::getStoreConfig('web/url/use_store', Mage::app()->getStore()->getId());
    }

    public function useRewrite() {
        return Mage::getStoreConfig('web/seo/use_rewrites', Mage::app()->getStore()->getId());
    }

    public function checkApp($appId = null, $appSecret = null) {
        $config = array(
            'appId' => ($appId) ? $appId : Mage::helper('fbintegrator')->getAppKey(),
            'secret' => ($appSecret) ? $appSecret : Mage::helper('fbintegrator')->getAppSecret(),
            'cookie' => false,
        );

        $facebook = new AW_FBIntegrator_Model_Facebook_Api($config);
        try {
            $session = $facebook->api('/19292868552');
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function likeEnabled() {
        return Mage::getStoreConfigFlag('fbintegrator/like/enabled');
    }

    public function likePosition() {
        return Mage::getStoreConfig('fbintegrator/like/position');
    }

    public function likeStyle() {
        return array(
            'laystyle' => Mage::getStoreConfig('fbintegrator/like/laystyle'),
            'face' => Mage::getStoreConfig('fbintegrator/like/face'),
            'width' => Mage::getStoreConfig('fbintegrator/like/width'),
            'verb' => Mage::getStoreConfig('fbintegrator/like/verb'),
            'font' => Mage::getStoreConfig('fbintegrator/like/font'),
            'color' => Mage::getStoreConfig('fbintegrator/like/color'),
            'send' => Mage::getStoreConfig('fbintegrator/like/send') ? 'true' : 'false',
        );
    }

    public function getAppConfig() {
        return array(
            'appId' => $this->getAppKey(),
            'secret' => $this->getAppSecret(),
            'cookie' => true,
        );
    }

    public function getMe() {
        $facebook = new AW_FBIntegrator_Model_Facebook_Api($this->getAppConfig());
        $userData = NULL;
        $session = $facebook->getUser();
        if ($session) {
            try {
                $userData = $facebook->api('/me');
            } catch (Exception $exc) {
                
            }
        }
        return $userData;
    }
    
    public function getRequiredAttributes() {
        $defaultRequired = array(
            'website_id',
            'store_id',
            'created_in',
            'firstname',
            'lastname',
            'email',
            'dob',
            'gender',
            'group_id',
            'reward_update_notification',
            'reward_warning_notification',
        );
        $requiredAttributes = array();
        $customerAttributes = Mage::getModel('customer/customer')->getAttributes();
        foreach ($customerAttributes as $attr) {
            if (
                    $attr->getIsRequired()
                    && ($attr->getIsVisible())
                    && (!in_array($attr->getAttributeCode(), $defaultRequired))
                    && (in_array('customer_account_create', $attr->getUsedInForms()))
            ) {
                $requiredAttributes[] = $attr;
            }
        }
        return $requiredAttributes;
    }

    public function getRequiredFields() {

        $attribues = array();

        $codes = array('prefix', 'suffix', 'taxvat');
        foreach ($codes as $code) {
            $att = Mage::getSingleton('eav/config')->getAttribute('customer', $code);

            $isRequired = ($att->getIsRequired()
                    || (Mage::getStoreConfig('customer/address/' . $code . '_show') == 'req')
                    );

            $attribues[$code] = array(
                'code' => $code,
                'name' => $att->getFrontendLabel(),
                'required' => $isRequired,
                'values' => explode(';', Mage::helper('customer/address')->getConfig($code . '_options')),
            );
        }

        return $attribues;
    }

    public function getCountRequiredFields() {
        $array = $this->getRequiredFields();
        $count = 0;
        foreach ($array as $field) {
            if ($field['required'])
                $count++;
        }

        $count += count($this->getRequiredAttributes());
        return $count;
    }

    public function getRequiredFormUrl() {
        return Mage::getUrl('fbintegrator/facebook/form');
    }

    public function getStoreLogo() {
        return Mage::getDesign()->getSkinUrl(Mage::getStoreConfig('design/header/logo_src'));
    }

    /**
     * Retrieve current url with port from store base url
     *
     * @return string
     */
    public function getCurrentUrlWithStorePort() {

        $isStoreSecure = Mage::app()->getStore()->isCurrentlySecure();

        $baseUrl = ($isStoreSecure) ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, TRUE) : Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        $storePort = parse_url($baseUrl, PHP_URL_PORT);

        $request = Mage::app()->getRequest();

        $url = $request->getScheme()
                . '://' . $request->getHttpHost()
                . ($storePort ? ':' . $storePort : '')
                . $request->getServer('REQUEST_URI');

        $url = Mage::getSingleton('core/url')->escape($url);

        return $url;
    }

    public function getUrlSecretCode($url = null) {
        if ($url === null) {
            $url = Mage::app()->getRequest()->getServer("REQUEST_URI");
        }

        $a = parse_url($url, PHP_URL_PATH);
        $a = md5(md5(self::SECRET_CODE_SALT . $a));
        return $a;
    }

    public function registerCustomer($data) {

        $customer = Mage::getModel('customer/customer')->setId(null);
        $customer->setData($data);
        $customer->getGroupId();
        $password = uniqid();
        $customer->setPassword($password);
        $customer->setConfirmation($password);
        $customer->save();

        if ($customer->getConfirmation() && $customer->isConfirmationRequired()) {
            $customer->setConfirmation(null);
            $customer->save();
        }

        return $customer;
    }

    public function getUserGender($data = array()) {
        $gender = '';
        if (isset($data['gender'])) {

            $genderAttribute = Mage::getResourceSingleton('customer/customer')
                    ->getAttribute('gender');

            if ($genderAttribute) {
                foreach ($genderAttribute->getSource()->getAllOptions() as $v) {
                    if (strtolower($v['label']) == strtolower($data['gender'])) {
                        $gender = $v['value'];
                        return $gender;
                    }
                }
            }
        }
        return $gender;
    }

}
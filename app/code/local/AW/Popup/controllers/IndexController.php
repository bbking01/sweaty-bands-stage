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
 * @package    AW_Popup
 * @version    1.2.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Popup_IndexController extends Mage_Core_Controller_Front_Action
{
    public function ajaxAction()
    {
        $response = new Varien_Object();
        $response->setError(0);
        try {
            $pageName = $this->getRequest()->getParam('page');
            if (!$pageName) {
                throw new Exception($this->__('PageId not found'));
            }
            $pageName = Mage::helper('core')->escapeHtml($pageName);
            $popup = Mage::helper('popup')->getPopup($pageName);

            if (isset($popup['popup_id'])) {
                Mage::helper('popup')->setViewedPopup($popup['popup_id']);
                $response->addData($popup->toArray());
                $autoHideTime = Mage::helper('popup')->getAutoHide();
                if ($autoHideTime > 0) {
                    $response->setAutoHideTime($autoHideTime);
                }

            } else {
                throw new Exception('Popup not found');
            }

        } catch (Exception $e) {
            $response->setError(1);
            $response->setErrorMessage($e->getMessage());
        }
        $this->getResponse()->setBody($response->toJson());
        return;
    }
}

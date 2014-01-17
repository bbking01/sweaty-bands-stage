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


class AW_Autorelated_Adminhtml_AbstractblockController extends Mage_Adminhtml_Controller_Action
{
    public function newConditionHtmlAction()
    {
        if ($this->_validateFormKey()) {
            $id = $this->getRequest()->getParam('id');
            $typeArr = $this->getRequest()->getParam('type') ? $this->getRequest()->getParam('type') : 'awautorelated-rule_condition_combine';
            $typeArr = explode('|', str_replace('-', '/', $typeArr));
            $type = $typeArr[0];
            $prefix = ($this->getRequest()->getParam('prefix')) ? $this->getRequest()->getParam('prefix') : 'conditions';
            $rule = ($this->getRequest()->getParam('rule')) ? base64_decode($this->getRequest()->getParam('rule')) : 'awautorelated/blocks';

            $model = Mage::getModel($type)
                ->setId($id)
                ->setType($type)
                ->setRule(Mage::getModel($rule))
                ->setPrefix($prefix);

            if (!empty($typeArr[1])) {
                $model->setAttribute($typeArr[1]);
            }

            if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
                $model->setJsFormObject($this->getRequest()->getParam('form'));
                $html = $model->asHtmlRecursive();
            } else {
                $html = '';
            }
            $this->getResponse()->setBody($html);
        }
    }

    protected function _filterDates($array, $dateFields)
    {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }

    /**
     * Returns true when admin session contain error messages
     */
    protected function _hasErrors()
    {
        return (bool)count($this->_getSession()->getMessages()->getItemsByType('error'));
    }

    /**
     * Set title of page
     *
     * @return AW_Autorelated_Adminhtml_AbstractblockController
     */
    protected function _setTitle($action)
    {
        if (method_exists($this, '_title')) {
            $this->_title($this->__('Automatic Related Products 2'))->_title($this->__($action));
        }
        return $this;
    }
}

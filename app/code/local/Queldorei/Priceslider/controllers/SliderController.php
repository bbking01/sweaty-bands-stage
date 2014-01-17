<?php

class Queldorei_Priceslider_SliderController extends Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);
        Mage::register('current_category', $category);
        $this->loadLayout();
        $this->renderLayout();
    }
}
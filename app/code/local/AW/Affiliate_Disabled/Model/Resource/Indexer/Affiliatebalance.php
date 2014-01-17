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
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Model_Resource_Indexer_Affiliatebalance extends Mage_Index_Model_Mysql4_Abstract
{
    const PAGE_SIZE = 100;

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_setResource('awaffiliate/affiliate');
        $this->_setMainTable(Mage::getModel('awaffiliate/affiliate')->getResource()->getMainTable());
    }

    public function refreshAffiliate($affiliateId)
    {
        $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
        if($affiliate->getData()) {
            $affiliate->recollectBalances()
                ->save();
        }
    }

    public function reindexAll()
    {
        $model = Mage::getModel('awaffiliate/affiliate');
        $resourceModel = $model->getResource();
        $resourceModel->beginTransaction();
        try {
            $collection = $model->getCollection();
            $collectionSize = $collection->getSize();
            $currentPage = 1;
            $pagesCount = intval(ceil($collectionSize / self::PAGE_SIZE));
            while($currentPage <= $pagesCount) {
                $collection = $model->getCollection();
                $collection->setPageSize(self::PAGE_SIZE);
                $collection->setCurPage($currentPage);
                foreach($collection as $item) {
                    $item->recollectBalances();
                    $item->save();
                }
                $currentPage++;
            }
            $resourceModel->commit();
        } catch (Exception $e) {
            $resourceModel->rollBack();
            throw $e;
        }
    }
}

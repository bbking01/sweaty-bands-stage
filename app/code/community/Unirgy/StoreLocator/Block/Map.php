<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 30.01.13
 * Time: 20:42
 *
 */

class Unirgy_StoreLocator_Block_Map
    extends Mage_Core_Block_Template
{
    const PAGE_VAR  = 'page';
    const LIMIT_VAR = 'limit';
    /**
     * @var Unirgy_StoreLocator_Model_Mysql4_Location_Collection
     */
    protected $_collection;
    /**
     * @var int
     */
    protected $_limit;

    /**
     * @var Unirgy_StoreLocator_Helper_Data
     */
    protected $_helper;

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getPager();

        if ($pagerBlock instanceof Varien_Object && $this->getCollection()) {

            /* @var $pagerBlock Mage_Page_Block_Html_Pager */
            $pagerBlock->setLimit($this->getLimit())
                ->setLimitVarName(self::LIMIT_VAR)
                ->setPageVarName(self::PAGE_VAR)
                ->setShowPerPage(false)
                ->setFrameLength(3)
                ->setJump(3)
                ->setCollection($this->getCollection())
                ->setData('show_amounts', false)
                ->setData('use_container', false);

            return $pagerBlock->toHtml();
        }

        return '';
    }

    /**
     * @return Mage_Page_Block_Html_Pager
     */
    public function getPager()
    {
        $pagerBlock = $this->getChild('ustorelocator.locations.pager');

        return $pagerBlock;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        if (!isset($this->_limit)) {
            $this->_limit = (int)Mage::getStoreConfig('ustorelocator/general/num_results');
        }

        return $this->_limit;
    }

    /**
     * @return Unirgy_StoreLocator_Model_Mysql4_Location_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @param Varien_Data_Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        if (!$collection instanceof Varien_Data_Collection) {
            Mage::log('Wrong collection passed: ' . get_class($collection), Zend_Log::ERR, 'sl.log', true);

            return $this;
        }
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }

        return $this;
    }

    public function getCurrentOrder()
    {
        return false; // todo
    }

    public function getCurrentDirection()
    {
        return 'ASC'; // todo
    }

    public function getCurrentPage()
    {
        if ($page = (int)$this->getRequest()->getParam(self::PAGE_VAR)) {
            return $page;
        }

        return 1;
    }

    public function getSlHelper()
    {
        if (!isset($this->_helper)) {
            $this->_helper = $this->helper('ustorelocator');
        }

        return $this->_helper;
    }
}
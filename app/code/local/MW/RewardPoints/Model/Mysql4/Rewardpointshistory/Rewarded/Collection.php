<?php

class MW_RewardPoints_Model_Mysql4_Rewardpointshistory_Rewarded_Collection extends MW_RewardPoints_Model_Mysql4_Rewardpointshistory_Collection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/rewardpointshistory');
    }
    
	public function setDateRange($from, $to)
	{
			$type_add_point = MW_RewardPoints_Model_Type::getAddPointArray();
			$type_subtract_point = MW_RewardPoints_Model_Type::getSubtractPointArray();
			//zend_debug::dump($type_add_point);die();
	        $this->_reset() ->addFieldToFilter('transaction_time', array('from' => $from, 'to' => $to, 'datetime' => true));
	        $this ->addFieldToFilter('status',MW_RewardPoints_Model_Status::COMPLETE);
	        //$this ->addFieldToFilter('type_of_transaction',array('in'=>array($type_subtract_point)));
			        
	        $this->addExpressionFieldToSelect('total_rewarded_sum','sum(if(type_of_transaction IN (1, 2, 3, 4, 5, 6, 14, 7, 8, 30, 15, 16, 12, 18, 21, 32, 25, 29, 26, 27, 19, 50, 51,52,53),amount,0))','total_rewarded_sum');
			$this->addExpressionFieldToSelect('rewarded_on_purchases_sum','sum(if(type_of_transaction IN (8,30),amount,0))','rewarded_on_purchases_sum');
	        $this->addExpressionFieldToSelect('rewarded_on_sign_up_sum','sum(if(type_of_transaction IN (1),amount,0))','rewarded_on_sign_up_sum');
			$this->addExpressionFieldToSelect('rewarded_on_subscribers_sum','sum(if(type_of_transaction IN (16),amount,0))','rewarded_on_subscribers_sum');
	        $this->addExpressionFieldToSelect('rewarded_on_reviews_sum','sum(if(type_of_transaction IN (2),amount,0))','rewarded_on_reviews_sum');
	        $this->addExpressionFieldToSelect('added_by_admin_sum','sum(if(type_of_transaction IN (12),amount,0))','added_by_admin_sum');
	        $this->addExpressionFieldToSelect('other_rewards_sum','sum(if(type_of_transaction IN (3, 4, 5, 6, 14, 7, 15, 18, 21, 32, 25, 29, 26, 27, 19, 50, 51,52,53),amount,0))','other_rewards_sum');
	        $this->addExpressionFieldToSelect('total_transaction_count','count( distinct if(type_of_transaction IN (1, 2, 3, 4, 5, 6, 14, 7, 8, 30, 15, 16, 12, 18, 21, 32, 25, 29, 26, 27, 19, 50, 51,52,53),history_id,null))','total_transaction_count');
			//$this->getSelect()->group(array('customer_id'));
	       // echo $this->getSelect();die();
	        return $this;
	}
 	public function setStoreIds($storeIds)
    {
        return $this;
    }
}
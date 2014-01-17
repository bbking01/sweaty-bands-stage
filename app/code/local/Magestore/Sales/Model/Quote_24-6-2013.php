<?php
require_once 'Mage/Sales/Model/Quote.php';
class Magestore_Sales_Model_Quote extends Mage_Sales_Model_Quote
{
	function objectsIntoArray($arrObjData, $arrSkipIndices = array())
	{
		$arrData = array();
		// if input is object, convert into array
		if (is_object($arrObjData)) {
			$arrObjData = get_object_vars($arrObjData);
		}
		
		if (is_array($arrObjData)) {
			foreach ($arrObjData as $index => $value) {
				if (is_object($value) || is_array($value)) {
					$value = $this->objectsIntoArray($value, $arrSkipIndices); // recursive call
				}
				if (in_array($index, $arrSkipIndices)) {
					continue;
				}
				$arrData[$index] = $value;
			}
		}
		return $arrData;
	}
	
	public function addProduct(Mage_Catalog_Model_Product $product, $request=null, $orderItem=null)
    {				
		if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = new Varien_Object(array('qty'=>$request));
        }
        if (!($request instanceof Varien_Object)) {
            Mage::throwException(Mage::helper('sales')->__('Invalid request for adding product to quote.'));
        }
	      $cartCandidates = $product->getTypeInstance(true)->prepareForCart($request, $product);

        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }
	    /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }
        $parentItem = null;
        $errors = array();      
	  
        foreach ($cartCandidates as $candidate) {
            $item = $this->_addCatalogProduct($candidate, $candidate->getCartQty());
            // Start - For design tool 
			$qty = $candidate->getCartQty();
			
			if(isset($_REQUEST['dataxml']) && !empty($_REQUEST['dataxml']))	
			{								
				$xml123 = simplexml_load_string($_REQUEST['dataxml']);	
				$xml = $this->objectsIntoArray($xml123);				
				$comment = $xml['comment'];
				
				if($comment)
				{
					$item->setComment($comment);
				}
				
				$front_image = $xml['front'];
				$item->setFrontImage($front_image);
				$back_image = $xml['back'];
				$item->setBackImage($back_image);
				$left_image = $xml['left'];
				$item->setLeftImage($left_image);
				$right_image = $xml['right'];
				$item->setRightImage($right_image);
				$total_color = $xml['noofcolor']['front']+$xml['noofcolor']['back']+$xml['noofcolor']['left']+$xml['noofcolor']['right'];				
				$item->setTotalcolor($total_color);
				$savestr = $xml['savestr'];
				$item->setSavestr($savestr);
				
				$cnt = 0;
				$i = 0;
				$number = array();
				$name = array();				
				$xml = simplexml_load_string($_REQUEST['pdfdata']);
				
				foreach($xml->savecode->accPrizing as $data)
				{					
					$name[$i] = trim($data->pName);
					$number[$i] = trim($data->pNumber);
					$i++;
				}				
				
				$nameparam = implode('.,;',$name);
				if($nameparam != '')
					$item->setNamevalue($nameparam);
				
				$numparam = implode('.,;',$number);				
				if($numparam != '')					
					$item->setNumbervalue($numparam);
								 					
				if(isset($_REQUEST['sourcefile']) && $_REQUEST['sourcefile'] != '')		
				{
					$item->setSourcefiledata($_REQUEST['sourcefile']);	
				}
								
				if(isset($_REQUEST['pdfdata']) && $_REQUEST['pdfdata'] != '')		
				{
					$item->setPdfdata($_REQUEST['pdfdata']);					
				}
			
			}else if(!empty($orderItem))
			{				
				$data = $orderItem->getData();
				$data = unserialize($data['product_options']);
				$xml = simplexml_load_string($data['info_buyRequest']['dataxml']);	
				
				$comment = $xml->comment;
								
				if($comment!= "null")
					$item->setComment($comment);
				else
					$item->setComment("");
				
				// Added by Naincy
				$front_image = $xml->front;
				$item->setFrontImage($front_image);
				$back_image = $xml->back;
				$item->setBackImage($back_image);

				$left_image = $xml->left;
				$item->setLeftImage($left_image);
				$right_image = $xml->right;
				$item->setRightImage($right_image);
				
				$orderData = $this->objectsIntoArray($xml);				
				
				$total_color = $orderData['noofcolor']['front']+$orderData['noofcolor']['back']+$orderData['noofcolor']['left']+$orderData['noofcolor']['right'];				
				
				$item->setTotalcolor($total_color);
				$savestr = $xml->savestr;
				$item->setSavestr($savestr);			
				$cnt = 0;
				$i = 0;
				$number = array();
				$name = array();				
				foreach ($xml->sizes->size->names->name as $key => $value )
				{	
					$value = ( array ) $value ;			
					if($cnt == $xml->sizes->size->quantity)		
					{
						$i = 0;
					}
					if($cnt >= $xml->sizes->size->quantity)
					{
						if($value [ 0 ] == 'NULL')
							$number[$i] = '';
						else
							$number[$i] = trim ( $value [ 0 ] );
					}else
					{
						if($value [ 0 ] == 'NULL')
							$name[$i] = '';							
						else
							$name[$i] = trim ( $value [ 0 ] );
					}	
  					$i++;	
					$cnt++;
				}		
				
				$nameparam = implode('.,;',$name);
				if($nameparam != '')
					$item->setNamevalue($nameparam);
				$numparam = implode('.,;',$number);				
				if($numparam != '')					
					$item->setNumbervalue($numparam);
			}		
			
			if(isset($_REQUEST['pdfdata']) && !empty($_REQUEST['pdfdata']))	
			{
				$front_image = str_replace('front','xml', $front_image);
				$file_parts = explode(".", $front_image);				
				$myFile = $file_parts[0].".xml";					
				$item->setXmlfile($myFile);
				$myFile = 'xml_files/'.$myFile;							
				$fh = fopen($myFile, 'w');
				$data = '<?xml version="1.0" encoding="iso-8859-1"?>';
				fwrite($fh, $data);			
				fwrite($fh, $_REQUEST['pdfdata']);			
				fclose($fh);								
			}					
			// End - For design tool 
            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
			$parentItem->setRowTotal($xml->totalprice);
            if ($parentItem && $candidate->getParentProductId()) {
                $item->setParentItem($parentItem);
            }

            /**
             * We specify qty after we know about parent (for stock)
             */
            $item->addQty($candidate->getCartQty());

            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $errors[] = $item->getMessage();
            }
        }
		
        if (!empty($errors)) {
            Mage::throwException(implode("\n", $errors));
        }

      //  Mage::dispatchEvent('sales_quote_product_add_after', array('items' => $items));
		  return $item;
    }

	
	public function getItemByProduct($product)
    {
        foreach ($this->getAllItems() as $item) {
		
		$frontImage  = $item->getFrontImage();
			if($frontImage==NULL && $_REQUEST['savestr'] == '')
			{
				if ($item->representProduct($product)) {
					return $item;
				}
			}
		
        }	
        return false;
    }
	
	
	/**
     * Merge quotes
     *
     * @param   Mage_Sales_Model_Quote $quote
     * @return  Mage_Sales_Model_Quote
     */
    public function merge(Mage_Sales_Model_Quote $quote)
    {
        Mage::dispatchEvent(
            $this->_eventPrefix . '_merge_before',
            array(
                $this->_eventObject=>$this,
                'source'=>$quote
            )
        );

        foreach ($quote->getAllVisibleItems() as $item) {
            $found = false;
            foreach ($this->getAllItems() as $quoteItem) {
                /*if ($quoteItem->compare($item)) {
                    $quoteItem->setQty($quoteItem->getQty() + $item->getQty());
                    $found = true;
                    break;
                } commented By Bhagyashri*/
            }

            if (!$found) {
                $newItem = clone $item;
                $this->addItem($newItem);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $newChild = clone $child;
                        $newChild->setParentItem($newItem);
                        $this->addItem($newChild);
                    }
                }
            }
        }
		/**
         * Init shipping and billing address if quote is new
         */
        if (!$this->getId()) {
            $this->getShippingAddress();
            $this->getBillingAddress();
        }

        if ($quote->getCouponCode()) {
            $this->setCouponCode($quote->getCouponCode());
        }

        Mage::dispatchEvent(
            $this->_eventPrefix . '_merge_after',
            array(
                $this->_eventObject=>$this,
                'source'=>$quote
            )
        );

        return $this;
    }	
	
	
	/**
     * Remove quote item by item identifier
     *
     * @param   int $itemId
     * @return  Mage_Sales_Model_Quote
     */
    public function removeItem($itemId)
    {
        $item = $this->getItemById($itemId);		
		
        if ($item) {
			$imageDir = Mage::getBaseDir(). DS .'designtool' . DS .'saveimg'. DS;
			$itemCollection = Mage::getModel("sales/quote_item")
							->getCollection()
							->addFieldToFilter("front_image", $item->getFrontImage())
							->addFieldToFilter('parent_item_id', array('neq' => 'NULL'));
			if(count($itemCollection->getData())==1)
			{
				if (file_exists($imageDir.$item->getFrontImage())){
					unlink($imageDir.$item->getFrontImage());
				}
				if (file_exists($imageDir.$item->getBackImage())){
					unlink($imageDir.$item->getBackImage());
				}
				if (file_exists($imageDir.$item->getLeftImage())){
					unlink($imageDir.$item->getLeftImage());
				}
				if (file_exists($imageDir.$item->getRightImage())){
					unlink($imageDir.$item->getRightImage());
				}
			}
		
            $item->setQuote($this);
            /**
             * If we remove item from quote - we can't use multishipping mode
             */
            $this->setIsMultiShipping(false);
            $item->isDeleted(true);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $child->isDeleted(true);
                }
            }

            $parent = $item->getParentItem();
            if ($parent) {
                $parent->isDeleted(true);
            }

            Mage::dispatchEvent('sales_quote_remove_item', array('quote_item' => $item));
        }

        return $this;
    }
}
?>
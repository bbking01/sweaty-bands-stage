<?php
class Magestore_Gallery_AccountController extends Mage_Core_Controller_Front_Action
{
	public function checkEmailDuplicationAction()
    {
        $write_old = Mage::getSingleton('core/resource')->getConnection('core_write');
     	$select="SELECT * FROM `customer_entity`";
        $row = $write_old->fetchRow($select);
           print_r($row);
        }
		}
	?>	
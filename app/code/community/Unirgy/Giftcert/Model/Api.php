<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-6-28
 * Time: 9:12
 */

class Unirgy_Giftcert_Model_Api
    extends Mage_Api_Model_Resource_Abstract
{
    /**
     * @param null $filters
     * @return array
     */
    public function items($filters = null)
    {
        $collection = $this->_getFilteredCollection($filters);
        if($collection->count() == 0) {
            $this->_fault('list_empty');
        }
        $result = array();
        foreach($collection as $item) {
            $result[] = $this->_prepareCertData($item);
        }
        return $result;
    }

    /**
     * @param array $filters
     * @return Unirgy_GiftCert_Model_Mysql4_Cert_Collection
     */
    protected function _getFilteredCollection($filters)
    {
        /* @var $collection Unirgy_GiftCert_Model_Mysql4_Cert_Collection */
        $collection = Mage::getModel('ugiftcert/cert')->getCollection();
        if(!empty($filters)) {
            try{
                foreach ($filters as $field => $value) {
                    if ($field == 'order_id') {
                        $collection->addOrderFilter($value);
                        continue;
                    } elseif ($field == 'order_item_id') {
                        $collection->addItemFilter($value);
                        continue;
                    }
                    $collection->addFieldToFilter($field, $value);
                }
            }catch(Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }
        return $collection;
    }

    /**
     * @param string $code
     * @return array
     */
    public function fetch($code)
    {
        $cert = $this->_getGcModel($code);
        if (!$cert->getId()) {
            $this->_fault('not_exists');
        }
        return $this->_prepareCertData($cert);
    }

    protected function _prepareCertData(Unirgy_Giftcert_Model_Cert $cert)
    {
        $cert->unsetData('_conditions');
        $attribs = array(
            'cert_id',
            'cert_number',
            'balance',
            'currency_code',
            'pin',
            'status',
            'expire_at',
            'recipient_name',
            'recipient_email',
            'recipient_address',
            'recipient_message',
            'store_id',
            'sender_name'
        );
        return $cert->toArray($attribs);
    }

    /**
     * $data = array(
     *  'cert_number' => fixed code or pattern ([AN*8]),
     *  'balance' => float (0.00),
     *  'currency_code' => three letter currency code (USD),
     *  'store_id' => integer (1),
     *  'status' => one of P (pending), A (active), I (inactive) (P) ,
     *  'expire_at' => date in format yyyy-mm-dd ,
     *  'sender_name' => string up to 100 chars,
     *  'pin' => fixed code or pattern ([N*4]),
     *  'recipient_name' => string up to 127 chars,
     *  'recipient_email' => email, string up to 127 chars,
     *  'recipient_message' => message text,
     *  'recipient_address' => address text,
     *  'comments' => comments text,
     *  'qty'' => integer, number of certificates to create (1)
     * )
     *
     * Returned is new certificate code, or in case that multiple certificates are created, concatenated codes
     * separated by comma.
     *
     * @param array $data
     * @return string
     */
    public function create($data)
    {
        $result = $this->_saveCertificate($data);
        return $result;
    }

    /**
     * @param array $items
     * @return string
     */
    public function massCreate ($items)
    {
        $failed = 0;
        foreach ($items as $data) {
            try {
                $newitems = explode(',', $this->create($data));
                foreach($newitems as $code) {
                    $result[] = $code;
                }
            } catch(Exception $e) {
                $failed++;
            }
        }
        $created = count($result);

        $result = implode(',', $result);
        if($created) {
            $result = 'Created ' . $created . ' items [' . $result . '].';
        }
        if($failed) {
            $result .= 'Failed ' . $failed . ' items';
        }
        return $result;
    }

    /**
     * @param string $code
     * @param array $data
     * @return boolean
     */
    public function update($code, $data)
    {
        $cert = $this->_getGcModel($code);
        if ($cert->getId()) {
            $data['id'] = $cert->getId();
            $result = $this->_saveCertificate($data);
            return (boolean) $result;
        }

        $this->_fault('not_updated', 'Incorrect certificate code passed.');
    }

    /**
     * @param array $items
     * @return string
     */
    public function massUpdate($items)
    {
        $updated = 0;
        $failed = 0;
        foreach ($items as $data) {
            $code = isset($data['cert_number']) ? $data['cert_number'] : null;
            if(null === $code) {
                $failed++;
                continue;
            }
            try {
                $this->update($code, $data);
                $updated++;
            } catch (Exception $e) {
                $failed++;
            }
        }
        $result = '';

        if($updated) {
            $result = 'Updated ' . $updated . ' items. ';
        }
        if($failed) {
            $result .= 'Failed ' . $failed . ' items';
        }
        return $result;
    }

    /**
     * @param string $code
     * @return boolean
     */
    public function delete($code)
    {
        $cert = $this->_getGcModel($code);
        if(!$cert->getId()) {
            $this->_fault('not_exists');
        }

        try {
            $cert->delete();
        }catch (Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
        }
        return true;
    }

    /**
     * @param array $items
     * @return string
     */
    public function massDelete($items)
    {
        $deleted = 0;
        $failed = 0;

        foreach ($items as $code) {
            try {
                $this->delete($code);
                $deleted++;
            }catch (Exception $e) {
                $failed++;
            }
        }
        $result = '';
        if($deleted) {
            $result = 'Deleted ' . $deleted . ' items. ';
        }
        if($failed) {
            $result .= 'Failed ' . $failed . ' items';
        }
        return $result;
    }

    /**
     * @param string $code
     * @return Unirgy_Giftcert_Model_Cert
     */
    protected function _getGcModel($code=null)
    {
        $model = Mage::getModel('ugiftcert/cert');
        if($code) {
            $model->load($code, 'cert_number');
        }
        return $model;
    }

    protected function _saveCertificate($certData)
    {
        $certId = false;
        if(isset($certData['cert_number'])){
            $certNumber = $certData['cert_number'];
            unset($certData['cert_number']);  // only data that should not be updated for existing certificates
        } else {
            $certNumber = '[AN*8]';
        }
        $certBalance = isset($certData['balance']) ? $certData['balance'] : 0;
        $certCurrency = isset($certData['currency_code']) ? $certData['currency_code'] : 'USD';
        $certStoreId = isset($certData['store_id']) ? $certData['store_id'] : 1;
        $certStatus = isset($certData['status']) ? $certData['status'] : 'P';
        $certExpire = isset($certData['expire_at']) ? $certData['expire_at'] : null;
        $certSender = isset($certData['sender_name']) ? $certData['sender_name'] : null;

        $certPin = isset($certData['pin']) ? $certData['pin'] : '[N*4]';
        $certRecipientName = isset($certData['recipient_name']) ? $certData['recipient_name'] : null;
        $certRecipientEmail = isset($certData['recipient_email']) ? $certData['recipient_email'] : null;
        $certRecipientMessage = isset($certData['recipient_message']) ? $certData['recipient_message'] : null;
        $certRecipientAddress = isset($certData['recipient_address']) ? $certData['recipient_address'] : null;
        $certComments = isset($certData['comments']) ? $certData['comments'] : null;
        $certQty = isset($certData['qty']) ? $certData['qty'] : null;

        /* @var $model Unirgy_Giftcert_Model_Cert */
        $model = Mage::getModel('ugiftcert/cert');
        $model->load($certNumber, 'cert_number');
        $id = $model->getId();
        $new = !$id;
        if(!$new) {
            $model->addData($certData);
        } else {
            $model->setBalance($certBalance)
                ->setCurrencyCode($certCurrency)
                ->setStoreId($certStoreId)
                ->setStatus($certStatus)
                ->setExpireAt($certExpire)
                ->setSenderName($certSender)
                ->setCertNumber($certNumber);
            if($certPin)
                $model->setPin($certPin);

            $model->setRecipientName($certRecipientName)
                ->setRecipientEmail($certRecipientEmail)
                ->setRecipientAddress($certRecipientAddress)
                ->setRecipientMessage($certRecipientMessage);
        }



        $data = array(
            'user_id' => $this->_getSession()->getUser()->getId(),
            'username' => $this->_getSession()->getUser()->getUsername(),
            'ts' => now(),
            'amount' => $model->getBalance(),
            'currency_code' => $model->getCurrencyCode(),
            'status' => $model->getStatus(),
            'comments' => $certComments,
            'action_code' => 'update',
        );

        if ($new) {
            $qty = (int)$certQty;
            if ($qty < 1) {
                $qty = 1;
            }

            $num = $model->getCertNumber();
            if (!Mage::helper('ugiftcert')->isPattern($num)) {
                if ($new && $qty > 1) {
                    $this->_fault('new_multiple');
                }

                $dup = Mage::getModel('ugiftcert/cert')->load($num, 'cert_number');
                if ($dup->getId() && ($new || $dup->getId() != $model->getId())) {
                    $this->_fault('new_duplicate');
                }
            }

            $data['action_code'] = 'create';

            $ids = array();
            for ($i = 0; $i < $qty; $i++) {
                $clone = clone $model;
                $clone->save();
                $clone->addHistory($data);
                $ids[] = $clone->getCertNumber();
            }
            $certId = (count($ids) == 1) ? $ids[0] : implode(',', $ids);
        } else {
            $model->save();
            $model->addHistory($data);
            $certId = $model->getCertNumber();
        }
        return $certId;
    }
}

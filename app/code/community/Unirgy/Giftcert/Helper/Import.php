<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Petar Dzhambazov
 */
class Unirgy_Giftcert_Helper_Import
    extends Mage_Core_Helper_Data
{
    const INS  = 1;
    const UPD  = 2;
    const BOTH = 3;
    const EVENT_PREFIX= "ugiftcert_import_";

    /**
     * New certificates
     *
     * @var array
     */
    protected $_new_certs = array();

    /**
     * Updated Certificates
     *
     * @var array
     */
    protected $_update_certs = array();

    /**
     * Allowed import fields
     *
     * @var array
     */
    protected static $_import_fields = array(
            "cert_number",
            "balance",
            "pin",
            "status",
            "currency_code",
            "expire_at",
            "recipient_name",
            "recipient_email",
            "recipient_address",
            "recipient_message",
            "store_id",
            "sender_name",
            "conditions_serialized",
            "pdf_template_id",
            "template",
            "customer_groups",
            "send_on",
            "comments",
            "disallow_coupons",
        );
    /**
     * Minimum fields that will be imported
     *
     * @var array
     */
    protected $_min_fields = array(
            'pin',
            'balance',
            'expire_at',
            'currency_code',
            'store_id',
            'status'
        );

    /**
     * Required import fields
     *
     * @var array
     */
    protected $_req_fields = array('cert_number');

    /**
     * Index of current import file fields
     *
     * @var array
     */
    protected $_import_idx = array();


    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    /**
     * @var Mage_Core_Model_Resource
     */
    protected $_core_resource;

    /**
     * Name of certificate table
     *
     * @var string
     */
    protected $_cert_table_name;

    /**
     * Name of history table
     *
     * @var string
     */
    protected $_history_table_name;

    /**
     * Field delimiter
     *
     * @var string
     */
    protected $_delim;

    /**
     * Enclosing string
     *
     * @var string
     */
    protected $_enclosure;

    /**
     * Current allowed action, should match one oh class constants
     *
     * @var int
     */
    protected $_allowed_action;

    /**
     * Array of fatal errors
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Array of warnings
     *
     * @var array
     */
    protected $_warnings = array();

    /**
     * Template for missing field warning
     *
     * @var string
     */
    protected $_missing_field_warning = 'Missing field "%s". Set "%s" for record "%d"';

    /**
     * Template for required field error
     *
     * @var string
     */
    protected $_missing_field_error = 'Missing required field "%s". Skipping record "%d".';

    /**
     * @return Mage_Core_Model_Resource
     */
    protected function _getCoreResource()
    {
        if (!isset($this->_core_resource)) {
            $this->_core_resource = Mage::getSingleton('core/resource');
        }
        return $this->_core_resource;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    public function getWrite()
    {
        if (!isset($this->_write)) {
            $this->_write = $this->_getCoreResource()->getConnection('ugiftcert_write');
        }
        return $this->_write;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    public function getRead()
    {
        if (!isset($this->_read)) {
            $this->_read = $this->_getCoreResource()->getConnection('ugiftcert_read');
        }
        return $this->_read;
    }

    /**
     * @param array $row
     *
     * @return bool
     */
    protected function _setHeadingIdx($row)
    {
        foreach ($row as $idx => $heading) {
            if (in_array(str_replace(' ', '_', strtolower($heading)), self::$_import_fields)) {
                $this->_import_idx[$idx] = strtolower($heading);
            }
        }
        if (!in_array('cert_number', $this->_import_idx)) {
            return false;
        }

        foreach ($this->_min_fields as $field) {
            if (!in_array($field, $this->_import_idx)) {
                $this->_import_idx[] = $field;
            }
        }

        return true;
    }

    /**
     * @param array $row
     * @param int   $row_num
     *
     * @return bool
     */
    protected function _importRow($row, $row_num)
    {
        Mage::dispatchEvent(self::EVENT_PREFIX . 'row_start', array('row' => $row));
        $data = $this->getInitialData($row);

        if (!isset($data['cert_number'])) {
            $this->_errors[] = $this->__($this->_missing_field_error, 'cert_number', $row_num);
            return false;
        }

        // set defaults for some data
        $this->checkStatus($data)
            ->checkBalance($data)
            ->checkStoreId($data, $row_num)
            ->checkCurrency($data, $row_num)
            ->checkPin($data, $row_num)
            ->checkExpire($data, $row_num);

        Mage::dispatchEvent(self::EVENT_PREFIX . 'row_after_data_check', array('row' => $data));
        $certTable = $this->_getCertTableName();
        $read      = $this->getRead();
        $select    = $read->select()->from($certTable, array('cert_id'))->where(
            '`cert_number`=?', $data['cert_number']
        );
        $result    = $read->fetchOne($select);
        if (!empty($result)) {
            Mage::dispatchEvent(self::EVENT_PREFIX . 'row_before_update', array('row' => $data));
            $this->_update_certs[$data['cert_number']] = $data;
        } else {
            Mage::dispatchEvent(self::EVENT_PREFIX . 'row_before_create', array('row' => $data));
            $this->_new_certs[$data['cert_number']] = $data;
        }

        return true;
    }

    /**
     * @param array $data
     * @param int   $row_num
     *
     * @return Unirgy_Giftcert_Helper_Import
     */
    protected function checkExpire(&$data, $row_num)
    {
        if (!isset($data['expire_at']) || empty($data['expire_at'])) {
            $data['expire_at'] = null;
            $this->_warnings[] = $this->__($this->_missing_field_warning, 'expire_at', $data['expire_at'], $row_num);
        } else {
            $date              = strtotime($data['expire_at']);
            $data['expire_at'] = date('Y-m-d', $date);
        }
        return $this;
    }

    /**
     * @param array $data
     * @param int   $row_num
     *
     * @return Unirgy_Giftcert_Helper_Import
     */
    protected function checkPin(&$data, $row_num)
    {
        if (!isset($data['pin']) || empty($data['pin'])) {
            $data['pin']       = null;
            $this->_warnings[] = $this->__($this->_missing_field_warning, 'pin', $data['pin'], $row_num);
        }
        return $this;
    }

    /**
     * @param array $data
     * @param int   $row_num
     *
     * @return Unirgy_Giftcert_Helper_Import
     */
    protected function checkCurrency(&$data, $row_num)
    {
        if (!isset($data['currency_code'])
            || empty($data['currency_code'])
            || !in_array($data['currency_code'], Mage::app()->getLocale()->getAllowCurrencies())
        ) {
            $data['currency_code'] = Mage::app()->getStore()->getDefaultCurrencyCode();
            $this->_warnings[]     = $this->__(
                $this->_missing_field_warning, 'currency_code', $data['currency_code'], $row_num
            );
        }
        return $this;
    }

    /**
     * @param array $data
     *
     * @return Unirgy_Giftcert_Helper_Import
     */
    protected function checkBalance(&$data)
    {
        if (!isset($data['balance']) || empty($data['balance']) || !is_numeric($data['balance'])) {
            $data['balance'] = 0;
        } else {
            $data['balance'] = (float)$data['balance'];
        }
        return $this;
    }

    protected function checkStoreId(&$data, $row_num)
    {
        if (!isset($data['store_id']) || empty($data['store_id'])) {
            $data['store_id']  = Mage::app()->getStore()->getId();
            $this->_warnings[] = $this->__($this->_missing_field_warning, 'store_id', $data['store_id'], $row_num);
        } elseif (!is_numeric($data['store_id'])) {
            $data['store_id'] = Mage::app()->getStore($data['store_id'])->getId();
        }
        return $this;
    }

    /**
     * Set correct status
     *
     * @param array $data
     *
     * @return Unirgy_Giftcert_Helper_Import
     */
    protected function checkStatus(&$data)
    {
        switch (strtoupper($data['status'])) {
            case 'A':
            case 'ACTIVE':
                $data['status'] = 'A';
                break;
            case 'P':
            case 'PENDING':
                $data['status'] = 'P';
                break;
            default :
                $data['status'] = 'I';
                break;
        }
        return $this;
    }

    /**
     * Make sure that all imported fields are mapped correctly
     *
     * @param $row
     *
     * @return array
     */
    protected function getInitialData($row)
    {
        $data = array();
        foreach ($this->_import_idx as $idx => $col) {
            if (!isset($row[$idx]) || empty($row[$idx])) {
                $row[$idx] = null;
            }
            $data[$col] = $row[$idx];
        }
        return $data;
    }

    /**
     * @param string $fileName
     *
     * @throws Exception
     * @return int|string
     */
    public function importFile($fileName)
    {
        Mage::dispatchEvent(self::EVENT_PREFIX . 'before_file_open', array('file_name' => $fileName));
        $fh = @fopen($fileName, 'r');
        if (!$fh) {
            throw new Exception($this->__("File %s could not be read", $fileName));
        }
        ini_set('auto_detect_line_endings', 1);
        $counter = false;
        $delim   = $this->getDelimiter();
        $encl    = $this->getEnclosure();
        while ($row = fgetcsv($fh, 0, $delim, $encl)) {
            if ($counter === false) {
                if (!$this->_setHeadingIdx($row)) {
                    // there was no cert_number column
                    throw new Exception($this->__("Certificate code column not found in file"));
                }
                $counter = 0;
                continue;
            }
            $this->_importRow($row, $counter++);
        }

        $log = basename($fileName) . '.log';
        if (!empty($this->_warnings)) {
            Mage::log(implode(PHP_EOL, $this->_warnings), Zend_Log::WARN, $log, true);
        }
        if (!empty($this->_errors)) {
            Mage::log(implode(PHP_EOL, $this->_errors), Zend_Log::ERR, $log, true);
        }

        $this->_processImports();
        return $counter;
    }

    protected function _processImports()
    {
        $action = $this->_getAction();
        $write  = $this->getWrite();
        $write->beginTransaction();
        try {
            if ($action == self::INS || $action == self::BOTH && !empty($this->_new_certs)) {
                $this->_insert();
            }

            if ($action == self::UPD || $action == self::BOTH) {
                $this->_update();
            }

            $write->commit();
            $this->_addHistory();
        } catch (Exception $e) {
            $write->rollBack();
            throw $e;
        }
        return true;
    }

    private function _getAction()
    {
        if (!isset($this->_allowed_action)) {
            //            $this->_allowed_action = Mage::getStoreConfig('ugiftcert/import/action');
            $this->_allowed_action = self::BOTH;
        }
        return $this->_allowed_action;
    }

    protected function _insert()
    {
        $write      = $this->getWrite();
        $cert_table = $this->_getCertTableName();
        $columns    = $this->_import_idx;
        $com        = array_search('comments', $columns);
        if ($com) {
            unset($columns[$com]);
        }
        foreach ($this->_new_certs as $data) {
            unset($data['comments']);
            $rows[] = $data;
        }

        $write->insertMultiple($cert_table, $rows);
    }

    protected function _update()
    {
        $write      = $this->getWrite();
        $cert_table = $this->_getCertTableName();
        foreach ($this->_update_certs as $data) {
            if (!isset($data['cert_number'])) {
                continue;
            }
            unset($data['comments']);
            $write->update($cert_table, $data, array('cert_number=?' => $data['cert_number']));
        }
    }

    protected function _addHistory()
    {
        if (empty($this->_new_certs) && empty($this->_update_certs)) {
            return null;
        }
        $write         = $this->getWrite();
        $history_table = $this->_getHistoryTableName();
        $read          = $this->getRead();
        $cert_table    = $this->_getCertTableName();
        $sel           = $read->select()->from($cert_table, array('cert_id'));
        $rows          = array();
        $session       = Mage::getSingleton('admin/session');
        if ($session) {
            $user_id  = $session->getUser()->getId();
            $username = $session->getUser()->getUsername();
        } else {
            $user_id  = null;
            $username = null;
        }
        $ts = now();

        foreach ($this->_new_certs as $cert_number => $data) {
            $data['cert_id']     = $this->_certCodeSelect($sel, $cert_number);
            $data['user_id']     = $user_id;
            $data['username']    = $username;
            $data['ts']          = $ts;
            $data['action_code'] = 'import';

            $rows[] = $this->_getHistoryRecord($data);
        }

        foreach ($this->_update_certs as $cert_number => $data) {

            $data['cert_id']     = $this->_certCodeSelect($sel, $cert_number);
            $data['user_id']     = $user_id;
            $data['username']    = $username;
            $data['ts']          = $ts;
            $data['action_code'] = 'update';

            $rows[] = $this->_getHistoryRecord($data);
        }

        $write->insertMultiple($history_table, $rows);
    }

    /**
     * @param Zend_Db_Select $sel
     * @param string $cert_number
     * @return Zend_Db_Expr
     */
    protected function _certCodeSelect($sel, $cert_number)
    {
        $sel->reset(Zend_Db_Select::WHERE);
        return new Zend_Db_Expr('(' . $sel->where('`cert_number`=?', $cert_number) . ')');
    }

    protected function _getHistoryRecord($data)
    {
        if (!isset($data['comments'])) {
            $data['comments'] = '';
        }
        return array(
            'cert_id'       => $data['cert_id'],
            'user_id'       => $data['user_id'],
            'username'      => $data['username'],
            'ts'            => $data['ts'],
            'amount'        => $data['balance'],
            'currency_code' => $data['currency_code'],
            'status'        => $data['status'],
            'comments'      => $data['comments'],
            'action_code'   => $data['action_code'],
        );
    }

    private function _getCertTableName()
    {
        if (!isset($this->_cert_table_name)) {
            $this->_cert_table_name = $this->_getCoreResource()->getTableName('ugiftcert/cert');
        }
        return $this->_cert_table_name;
    }

    protected function _getHistoryTableName()
    {
        if (!isset($this->_history_table_name)) {
            $this->_history_table_name = $this->_getCoreResource()->getTableName('ugiftcert/history');
        }
        return $this->_history_table_name;
    }

    public function getDelimiter()
    {
        return Mage::getStoreConfig('ugiftcert/import/delimiter');
    }

    /**
     * @return array
     */
    public static function getImportFields()
    {
        return self::$_import_fields;
    }

    public function getEnclosure()
    {
        return Mage::getStoreConfig('ugiftcert/import/enclosure');
    }
}

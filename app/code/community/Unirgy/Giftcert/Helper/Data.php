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
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Helper_Data extends Mage_Core_Helper_Data
{

    const LOG_NAME = 'giftcert.log';

    /**
     * Convert pattern to random string
     *
     * Example: [A*5]-[AN*8]-[N*4]
     *
     * @param string $pattern
     * @return string
     */
    public function processRandomPattern($pattern)
    {
        return preg_replace_callback('#\[([AN]{1,2})\*([0-9]+)\]#', array($this, 'convertPattern'), $pattern);
    }

    /**
     * Random pattern regex callback method
     *
     * @param array $m
     * @return string
     */
    public function convertPattern($m)
    {
        $chars = (strpos($m[1], 'A') !== false ? 'ABCDEFGHJKLMNPQRSTUVWXYZ' : '') .
            (strpos($m[1], 'N') !== false ? '23456789' : '');
        // no confusing chars, like O/0, 1/I
        return $this->getRandomString($m[2], $chars);
    }

    /**
     * Check whether parameter is a random pattern
     *
     * @param string $str
     * @return int
     */
    public function isPattern($str)
    {
        return preg_match('#\[([AN]{1,2})\*([0-9]+)\]#', $str);
    }

    /**
     * Possible info_buyRequest variables
     *
     * @return array
     */
    public function getGiftcertOptionVars()
    {
        $config = Mage::getConfig()->getNode('global/ugiftcert/options/vars');
        $vars   = array();
        foreach ($config->children() as $node) {
            $vars[$node->getName()] = $this->__((string)$node->label);
        }

        return $vars;
    }

    /**
     * Generate options for item line in order view
     *
     * @param array $result
     * @param Mage_Sales_Model_Order_Item $item
     */
    public function addOrderItemCertOptions(&$result, $item)
    {
        if ($item->getProductType() !== 'ugiftcert') {
            return;
        }

        if ($options = $item->getProductOptionByCode('info_buyRequest')) {
            foreach ($this->getGiftcertOptionVars() as $code=> $label) {
                if (!empty($options[$code])) {
                    $value = $options[$code];
                    if($code == 'delivery_type'){
                        $value = ($value == 'virtual') ? $this->__("By Email") : $this->__("By Post");
                    }
                    $result[] = array(
                        'label'        => $label,
                        'value'        => $value,
                        'option_value' => $value,
                    );
                }
            }
        }

        if (empty($options['recipient_email']) && empty($options['recipient_address'])) {
            $giftcerts = Mage::getModel('ugiftcert/cert')->getCollection()->addItemFilter($item->getId());
            if ($giftcerts->count()) {
                $gcs = array();
                foreach ($giftcerts as $gc) {
                    $gcs[] = $gc->getCertNumber();
                }
                $gcsStr   = join("\n", $gcs);
                $result[] = array(
                    'label'        => $this->__('Certificate number(s)'),
                    'value'        => $gcsStr,
                    'option_value' => $gcsStr,
                );
            }
        }
    }

    protected $_currencies = array();

    public function getCurrency($currencyCode)
    {
        if (!isset($this->_currencies[$currencyCode])) {
            $this->_currencies[$currencyCode] = Mage::getModel('directory/currency')->load($currencyCode);
        }
        return $this->_currencies[$currencyCode];
    }

    protected $_productCache = array();

    public function getPdfPrintout(Varien_Object $data)
    {
        if (Mage::getStoreConfig('ugiftcert/email/pdf_enabled')) {
            /* @var $printout Unirgy_Giftcert_Model_Pdf_Printout */
            $printout = Mage::getModel('ugiftcert/pdf_printout', array('cert_data' => $data));
            $pdf      = $printout->getPdf();
            return $pdf;
        }
        return false;
    }

    public function outputPdfPrintout(Varien_Object $data)
    {
        $pdf = $this->getPdfPrintout($data);
        if ($pdf) {
            return $pdf->render();
        }
    }

    /**
     * Parse GC amount configuration
     *
     * Used on product list to determine minimal price, and on product view page.
     *
     * Format:
     * 50-1500 : range
     * 50;100;200 : dropdown
     * 100 : fixed amount
     * - : any value
     *
     * Multi currency setup - use multiple lines, star for default:
     *
     * USD:50-1500
     * CAD,EUR:50;100;200
     * *:25;50
     *
     * Whitespaces are ignored
     *
     * @param mixed $product
     * @param mixed $attr
     * @return mixed
     */
    public function getAmountConfig($product, $attr = 'ugiftcert_amount_config')
    {
        $valuesStr = $product->getDataUsingMethod($attr);

        if (!$valuesStr) {
            $valuesStr = Mage::getStoreConfig('ugiftcert/default/amount_config');
        }

        $valuesStr = trim(str_replace(array(' ', "\r", "\t"), '', $valuesStr));
        $valuesStr = rtrim($valuesStr, ';');

        $currencyCode = Mage::app()->getStore()->getCurrentCurrency()->getCurrencyCode();
        $lines        = explode("\n", $valuesStr);
        if (sizeof($lines) > 1) {
            /*
             * If more than one row is found, parse rows for currency options, if one of currency options matches current
             * store currency, it will be used, then if there is catch all option * it will be used when there is no match
             * if nothing matches, then any amount is used
             */
            $choices = array();
            foreach ($lines as $line) {
                $values = explode(':', $line);
                if (empty($values[1])) {
                    continue;
                }
                $choices[$values[0]] = $values[1];
            }
            $found = false;
            foreach ($choices as $curs=> $values) {
                if (strpos($curs, $currencyCode) !== false) {
                    $found     = true;
                    $valuesStr = $values;
                }
            }
            if (!$found) {
                $valuesStr = isset($choices['*']) ? $choices['*'] : '-';
            }
        } elseif (sizeof($lines) == 1) {
            $values = explode(':', $lines[0]);
            if (!empty($values[1])) { // one line with currency code
                $valuesStr = $values[1];
            }
        }

        if ($valuesStr === '' || $valuesStr === '-') {
            return array('type'=> 'any');
        }

        $locale = Mage::app()->getLocale();

        $values = explode('-', $valuesStr);
        if (sizeof($values) == 2) {
            return array(
                'type'=> 'range',
                'from'=> $locale->getNumber($values[0]),
                'to'  => $locale->getNumber($values[1]),
            );
        }

        $values = explode(';', $valuesStr);
        foreach ($values as $idx => $price) {
            if ($price !== 0 && empty($price)) {
                unset($values[$idx]); // if non zero empty value, remove it. Leave zero as option to have.
            } else {
                $values[$idx] = $locale->getNumber($price);
            }
        }
        if (sizeof($values) > 1) {
            return array('type'=> 'dropdown', 'options'=> $values);
        }

        $value = $locale->getNumber($valuesStr);
        return array('type'=> 'fixed', 'amount'=> $value);
    }

    protected $_currencyRate;

    public function reverseCurrency($amount, $origAmount)
    {
        if (empty($this->_currencyRate)) {
            $this->_currencyRate = Mage::app()->getStore()->getCurrentCurrencyRate();
        }
    }

    protected $_store;
    protected $_oldStore;
    protected $_oldArea;
    protected $_oldDesign;

    /**
     * Safely set frontend configuration for sending emails
     *
     * @param mixed $store
     * @return Unirgy_Giftcert_Helper_Data
     */
    public function setDesignStore($store = null)
    {
        if (!is_null($store)) {
            if ($this->_store) {
                return $this;
            }
            $this->_oldStore = Mage::app()->getStore();
            $this->_oldArea  = Mage::getDesign()->getArea();
            $this->_store    = Mage::app()->getStore($store);

            $store   = $this->_store;
            $area    = 'frontend';
            $package = Mage::getStoreConfig('design/package/name', $store);
            $design  = array('package'=> $package, 'store'=> $store->getId());
            $inline  = false;
        } else {
            if (!$this->_store) {
                return $this;
            }
            $this->_store = null;
            $store        = $this->_oldStore;
            $area         = $this->_oldArea;
            $design       = $this->_oldDesign;
            $inline       = true;
        }

        Mage::app()->setCurrentStore($store);
        $oldDesign = Mage::getDesign()->setArea($area)->setAllGetOld($design);
        Mage::app()->getTranslator()->init($area, true);
        Mage::getSingleton('core/translate')->setTranslateInline($inline);

        if ($this->_store) {
            $this->_oldDesign = $oldDesign;
        } else {
            $this->_oldStore  = null;
            $this->_oldArea   = null;
            $this->_oldDesign = null;
        }

        return $this;
    }

    public function addAdminhtmlVersion($module = 'Unirgy_Giftcert')
    {
        $layout  = Mage::app()->getLayout();
        $version = (string)Mage::getConfig()->getNode("modules/{$module}/version");

        $layout->getBlock('before_body_end')->append($layout->createBlock('core/text')->setText('
            <script type="text/javascript">$$(".legality")[0].insert({after:"' . $module . ' ver. ' . $version . '<br/>"});</script>
        '));

        return $this;
    }

    public function isModuleActive($code)
    {
        $module = Mage::getConfig()->getNode("modules/$code");
        $model  = Mage::getConfig()->getNode("global/models/$code");
        return $module && $module->is('active') || $model;
    }

    public function compareMageVer($ceVer, $eeVer = null, $op = '>=')
    {
        return $this->isModuleActive('Enterprise_Enterprise')
            ? version_compare(Mage::getVersion(), !is_null($eeVer) ? $eeVer : $ceVer, $op)
            : version_compare(Mage::getVersion(), $ceVer, $op);
    }

    /**
     * Moved from Price model here so that it can be reused
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getPrice($product)
    {
        $amountConfig = $this->getAmountConfig($product);
        switch ($amountConfig['type']) {
            case 'range':
                $price = $amountConfig['from'];
                break;
            case 'dropdown':
                $o     = $amountConfig['options'];
                $price = $o[0] ? $o[0] : $o[1];
                break;
            case 'fixed':
                $price = $amountConfig['amount'];
                break;
            default:
                $price = 0;
        }
        return $price;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param float $amount
     * @return bool
     */
    public function validateAmount($product, $amount)
    {
        $valid        = true;
        $amountConfig = $this->getAmountConfig($product);
        switch ($amountConfig['type']) {
            case 'range': // amount should be more than minimum and less than maximum set
                $valid = ($amountConfig['from'] <= $amount && $amountConfig['to'] >= $amount);
                break;
            case 'dropdown': // amount should be one of predefined options
                $o     = $amountConfig['options'];
                $valid = in_array($amount, $o);
                break;
            case 'fixed': // amount should be equal to configured amount
                $valid = ($amountConfig['amount'] == $amount);
                break;
            default: // any value
                $valid = true;
        }
        return $valid;
    }

    public function validateConditions(Unirgy_Giftcert_Model_Cert $cert, $quote)
    {
        if (Mage::getStoreConfig('ugiftcert/default/use_conditions') && $cert->getConditions()) {
            if ($quote->isVirtual()) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
            return $cert->getConditions()->validate($address);
        }
        return false;
    }

    /**
     * @param Unirgy_Giftcert_Model_Cert $cert
     * @return Unirgy_Giftcert_Model_Cert
     */
    public function loadConditionData(Unirgy_Giftcert_Model_Cert $cert = null)
    {
        $data = $this->getRequest()->getPost();
        if (!$data || !isset($data['rule'])) {
            return $cert;
        }

        if (isset($data['rule']['conditions'])) {
            $args['conditions'] = $data['rule']['conditions'];
            if (null == $cert) {
                $cert = Mage::getModel('ugiftcert/cert');
            }
            $cert->loadPost($args);
        }
        return $cert;
    }

    public function processArraySettings($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (!is_array($value)) { // processing multidimensional arrays only
                continue;
            }
            if (!empty($value['delete'])) {
                unset($data[$key]);
            } else {
                unset($data[$key]['delete']);
            }
        }
        return $data;
    }

    /**
     * Load pdf settings from POST
     * If parameter serialize is passed as true, return serialized value.
     *
     * @param bool $serialize
     * @return array|int|null|string
     */
    public function loadPdfSettings($serialize = true)
    {
        $pdfSettingsName = 'settings';
        $data        = $this->getRequest()->getPost($pdfSettingsName);
        $pdfSettings = array();

        $settingsFieldNames = $this->getPdfSettingsFields();
        foreach ($settingsFieldNames as $field) {
            if (isset($data[$field])) {
                $value = $this->processArraySettings($data[$field]);
                if (!empty($value)) {
                    $pdfSettings[$field] = $value;
                }
            }
        }
        if (empty($pdfSettings)) {
            $pdfSettings = null;
        } else {
            $configDataModel = $this->getConfigDataModel();

            $uploadedFiles = $this->processImageSettings($configDataModel, 'image_settings');

            if (!empty($uploadedFiles)) {
                foreach ($uploadedFiles as $file) {
                    $key                                          = $file['idx'];
                    $value                                        = $file['upload_dir'] . DS . $file['file'];
                    $pdfSettings['image_settings'][$key]['url']   = $value;
                    $pdfSettings['image_settings'][$key]['value'] = $file['file'];
                }
            }
        }
        if ($serialize) {
            $pdfSettings = Zend_Json::encode($pdfSettings);
        }
        return $pdfSettings;
    }

    /**
     * @return Unirgy_Giftcert_Model_Config_Data
     */
    public function getConfigDataModel()
    {
        /* @var $configDataModel Unirgy_Giftcert_Model_Config_Data */
        $configDataModel = Mage::getModel('ugiftcert/config_data')
            ->setSection('ugiftcert')
            ->setWebsite(Mage::app()->getWebsite()->getCode())
            ->setStore(Mage::app()->getStore()->getCode())
            ->prepare();
        return $configDataModel;
    }

    public function processImageSettings($configDataModel, $name)
    {
        $uploadedFiles = array();
        if (isset($_FILES[$name]['name']) && is_array($_FILES[$name]['name'])) {
            $uploadedFiles = $this->_processUploadedImages($configDataModel, 'pdf', $name);
        }
        return $uploadedFiles;
    }

    protected function _processUploadedImages(Mage_Adminhtml_Model_Config_Data $configData, $subPath, $elementName)
    {
        $scope   = $configData->getScope();
        $scopeId = $configData->getScopeId();

        $uploadDir = 'unirgy/giftcert/' . $subPath . '/' . $scope;
        if ($scope && $scope != 'default') {
            $uploadDir .= '/' . $scopeId;
        }

        $uploadRoot = (string)Mage::getConfig()->getNode('system/filesystem/media', $scope, $scopeId);
        $uploadRoot = Mage::getConfig()->substDistroServerVars($uploadRoot);
        $files      = array();
        try {
            foreach ($_FILES[$elementName]['name'] as $key => $name) {
                if (empty($name['file'])) continue;
                $file['name']     = $name['file'];
                $file['tmp_name'] = $_FILES[$elementName]['tmp_name'][$key]['file'];
                $file['error']    = $_FILES[$elementName]['error'][$key]['file'];
                $file['size']     = $_FILES[$elementName]['size'][$key]['file'];
                $files[]          = $this->uploadImage($file, $key, $uploadRoot, $uploadDir);
            }

        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }

        return $files;
    }

    protected function uploadImage($file, $key, $uploadRoot, $uploadDir)
    {
        $result = array();
        // earlier Magento versions lack Mage_Core_Model_File_Uploader
        try {
            if (class_exists('Mage_Core_Model_File_Uploader')) {
                $uploader = new Mage_Core_Model_File_Uploader($file);
            } elseif (class_exists('Varien_File_Uploader')) {
                $uploader = new Varien_File_Uploader($file);
            }
        } catch (Exception $e) {
            if ('File was not uploaded.' == $e->getMessage()) {
                // 1.4.1.1 does not add code to thrown error, therefore
                // compare to exception message and not its code
                switch ($file['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $msg = $this->__("File %s exceeds the 'upload_max_filesize' directive in php.ini.", $file['name']);
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $msg = $this->__("File %s exceeds the 'MAX_FILE_SIZE' directive that was specified in the HTML form.", $file['name']);
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $msg = $this->__("File %s was only partially uploaded.", $file['name']);
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $msg = $this->__("File %s was uploaded.", $file['name']);
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $msg = $this->__("Missing a temporary folder. Could not save file %s", $file['name']);
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $msg = $this->__("Failed to write file to disk. Could not save file %s", $file['name']);
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $msg = $this->__("A PHP extension stopped file %s upload.", $file['name']);
                        break;
                    default :
                        $msg = '';
                        break;
                }
                if($msg){
                    Mage::getSingleton('core/session')->addError($msg);
                }
                Mage::getSingleton('core/session')->addError($e->getMessage());
                return null;
            } else {
                try {
                    if (class_exists('Varien_File_Uploader')) {
                        $uploader = new Varien_File_Uploader($file);
                    }
                } catch (Exception $e) {
                    Mage::log('Uploader not found', Zend_Log::WARN, self::LOG_NAME, true);
                    Mage::log($e->getMessage(), Zend_Log::WARN, self::LOG_NAME, true);
                }
            }
        }

        if (!$uploader) {
            Mage::getSingleton('core/session')->addError($this->__('Could not find file uploader.'));
            return;
        }
        $uploader->setAllowedExtensions(array('tif', 'tiff', 'png', 'jpg', 'jpe', 'jpeg'));
        $uploader->setAllowRenameFiles(true);
        $result   = $uploader->save($uploadRoot . DS . $uploadDir);
        $filename = $result['file'];
        if ($filename) {
            $result['upload_dir'] = $uploadDir;
            $result['idx']        = $key;
        }
        return $result;
    }

    public function getPdfSettingsFields()
    {
        return array(
            'units',
            'use_font',
            'page_width',
            'page_height',
            'text_settings',
            'image_settings'
        );
    }

    /**
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest()
    {
        return Mage::app()->getRequest();
    }

    /**
     * Check if provided domain is in allowed domains list.
     *
     * @param string $domain
     * @return bool
     */
    public function checkDomain($domain)
    {
        $allow           = true;
        $allowed_domains = $this->getAllowedDomains();
        if (!empty($allowed_domains)) {
            $allow = false;
            foreach ((array)$allowed_domains as $test) {
                if (strpos($domain, $test) !== false) {
                    $allow = true;
                    break;
                }
            }
        }
        return $allow;
    }

    /**
     * @return array|bool
     */
    protected function getAllowedDomains()
    {
        $domains = trim(Mage::getStoreConfig('ugiftcert/default/valid_domains'));
        if (!empty($domains)) {
            return explode(',', $domains);
        }
        return false;
    }

    public function escape($data, $allowedTags = null)
    {
        if (method_exists($this, 'escapeHtml')) {
            return $this->escapeHtml($data, $allowedTags);
        }
        return $this->htmlEscape($data, $allowedTags);
    }

    public function isCertificateAllowed(Mage_Sales_Model_Quote $quote)
    {
        $quote->collectTotals();
        if ($quote->getGiftcertCode() && Mage::getStoreConfig('ugiftcert/default/single_mode')) {
            Mage::throwException(Mage::getStoreConfig('ugiftcert/default/single_mode_error_msg'));
        } elseif ($quote->getCouponCode() && Mage::getStoreConfig('ugiftcert/default/disallow_coupons')) {
            // not sure about this. Do we not allow certificate if there is already coupon?
            Mage::throwException(Mage::getStoreConfig('ugiftcert/default/disallow_coupons_error_msg'));
        }
        return true;
    }

    /**
     * Check if certificate store limitation is in place and if it is fulfilled.
     *
     * @param Unirgy_Giftcert_Model_Cert $cert
     *
     * @return bool
     */
    public function certificateStoreMatches($cert)
    {
        $shouldCheck = Mage::getStoreConfig('ugiftcert/default/check_store');
        if ($shouldCheck) {
            // if store check is enabled
            $certificateStoreId = $cert->getStoreId();
            $storeId            = Mage::app()->getStore()->getId();
            if ($certificateStoreId != 0 && $storeId != 0 && $certificateStoreId != $storeId) {
                // if both certificate and current stores are not admin, and they do not match
                return false;
            }
        }
        return true;
    }

    /**
     * Check if certificate customer group limitation is in place and if it is fulfilled.
     *
     * @param Unirgy_Giftcert_Model_Cert $cert
     *
     * @return bool
     */
    public function customerGroupMatches($cert)
    {
        $shouldCheck = Mage::getStoreConfig('ugiftcert/custom/apply_customer_group_limitation');
        if ($shouldCheck) {
            // if customer group check is enabled
            $certificateGroupIds = explode(',', $cert->getData('customer_groups'));
            $customerGroupId     = Mage::getSingleton('customer/session')->getCustomerGroupId();
            if (!in_array($customerGroupId, $certificateGroupIds)) {
                // if current customer group id is not in allowed list, return false
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $code
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     * @throws Unirgy_Giftcert_Exception_Coupon
     */
    public function addCertificate($code, Mage_Sales_Model_Quote $quote)
    {
        /* @var $cert Unirgy_Giftcert_Model_Cert */
        $cert = Mage::getModel('ugiftcert/cert')->load($code, 'cert_number'); // try to load certificate
        if ($cert->getId() && $cert->getStatus()=='A' && $cert->getBalance() > 0) { // if loaded
            if (!$this->certificateStoreMatches($cert)) {
                Mage::throwException($this->__("Certificate cannot be used in current store."));
            }
            if (!$this->customerGroupMatches($cert)) {
                Mage::throwException($this->__("Certificate cannot be used by current customer. If you are not logged in, log in and try again."));
            }
            $quote->collectTotals();
            if ($quote->getGiftcertCode() && Mage::getStoreConfig('ugiftcert/default/single_mode')) {
                Mage::throwException(Mage::getStoreConfig('ugiftcert/default/single_mode_error_msg'));
            }
            if(Mage::getStoreConfig('ugiftcert/default/use_conditions')) {
                $this->loadProducts($quote);
                $valid = $this->validateConditions($cert, $quote); // validate conditions if used.
                if(!$valid) {
                    Mage::throwException($this->__("Gift certificate '%s' cannot be used with your cart items", $cert->getCertNumber()));
                }
            }
            $cert->addToQuote($quote);
            if ($quote->getCouponCode() === $code) { // clear coupon if cert code is added before this moment
                $quote->setCouponCode('');
            } else if($this->areCouponsBanned($quote) && $quote->getCouponCode()){ // if there is previous coupon, remove it
                $quote->setCouponCode('');
                Mage::getSingleton('checkout/session')->addError(Mage::getStoreConfig('ugiftcert/default/disallow_coupons_error_msg'));
            }
            $quote->save()->setTotalsCollectedFlag(null);
            return true;
        } elseif($quote->getGiftcertCode() && $this->areCouponsBanned($quote)) {
            // if there are certificates applied already, and posted code is possibly coupon, and coupons are not allowed with
            // certificates, throw an exception, this will only work when capturing controller coupon Post
            $collection = Mage::getModel('salesrule/coupon')->getCollection()->addFieldToFilter('code', $code);
            if ($collection->count()) {
                throw new Unirgy_Giftcert_Exception_Coupon(Mage::getStoreConfig('ugiftcert/default/disallow_coupons_error_msg'));
            }
        }
        return false;
    }

    /**
     * Make sure products are fully loaded before conditions are evaluated
     *
     * @param Mage_Sales_Model_Quote_Address | Mage_Sales_Model_Quote $parent
     */
    public function loadProducts($parent)
    {
        foreach ($parent->getAllItems() as $object) {
            /* @var $object Mage_Sales_Model_Quote_Item */
            if ($object->getProduct() instanceof Mage_Catalog_Model_Product) {
                $product = $object->getProduct();
                if(!$product->getData('ugiftcert_loaded')){
                    $product->load($product->getId());
                    $product->setData('ugiftcert_loaded', true);
                }
            } else {
                $product = Mage::getModel('catalog/product')
                    ->load($object->getProductId());
                $product->setData('ugiftcert_loaded', true);
            }
        }
    }

    /**
     * Are coupons banned for this quote
     * Check if global coupon disabling is on, if it is,
     * check if certificates already added to order, ban coupons.
     *
     * If per certificate setting is enabled,
     * then try to get applied certificates and test if they have 'disallow_coupons' set to 1
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function areCouponsBanned($quote)
    {
        $banned = Mage::getStoreConfig('ugiftcert/default/disallow_coupons');
        if(!$banned){
            return false;
        }
        $perCert = Mage::getStoreConfig('ugiftcert/default/disallow_per_cert');
        if($perCert){
            $codes = $quote->getData('giftcert_code');
            if ($codes) {
                $c = array_map('trim', explode(',', $codes));
                /* @var $collection Unirgy_GiftCert_Model_Mysql4_Cert_Collection */
                $collection = Mage::getModel('ugiftcert/cert')->getCollection();
                $collection
                    ->addFieldToSelect('disallow_coupons')
                    ->addFieldToFilter('cert_number', array('in' => $c))
                    ->addFieldToFilter('disallow_coupons', 1);
                $count = $collection->count();
                return $count > 0;
            }
        }
        return true;
    }
}

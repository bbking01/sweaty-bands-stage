<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-6-30
 * Time: 13:00
 */

class Unirgy_Giftcert_Model_Pdf_Printout
    extends Mage_Sales_Model_Order_Pdf_Abstract
{
    const GLOBAL_CONFIG_CERT    = 'ugiftcert/pdf';
    const GLOBAL_CONFIG_PDF_TPL = 'ugiftcert/email/pdf_template';

    protected $_certificate_data;

    protected $_use_font;

    protected $_unit;

    protected $_pdf_settings;

    protected function _construct()
    {
        $gcdata = $this->getCertificateData();
        if (!$gcdata) {
            throw new Exception(Mage::helper('ugiftcert')
                ->__('Printout object is not instantiated correctly. Must pass gift certificate data to constructor call.'));
        }
    }

    /**
     * Retrieve PDF
     *
     * @return Zend_Pdf
     */
    public function getPdf()
    {
        $this->_beforeGetPdf();

        $store = $this->getCertificateData()->getStore();
        $pdf   = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style    = new Zend_Pdf_Style();
        $pageSize = $this->_getPageSize();
        $this->_setFont($style, 10, 'bold', $store);
        if ($store && $store->getId()) {
            Mage::app()->getLocale()->emulate($store->getId());
        }
        $page = $this->newPage(array('page_size' => $pageSize));
        $this->insertImages($page, $store);
        $this->insertFields($page);

        $this->_afterGetPdf();

        if ($store->getId()) {
            Mage::app()->getLocale()->revert();
        }

        return $pdf;
    }

    /**
     * @param Zend_Pdf_Page $page
     * @param null $store
     * @return void
     */
    public function insertImages(&$page, $store)
    {
        $settings    = $this->getSettings();
        $imgSettings = isset($settings['image_settings']) ? $settings['image_settings'] : array();
        foreach ($imgSettings as $image) {
            $img = $image['url'];
            $img = Mage::getStoreConfig('system/filesystem/media', $store) . DS . $img;
            if (strpos($img, '{{') !== false) {
                $img = Mage::getConfig()->substDistroServerVars($img);
            }
            if (is_file($img)) {
                $img       = Zend_Pdf_Image::imageWithPath($img);
                $imgWidth  = $this->_calculateDistance($image['width']);
                $imgWidth  = $imgWidth ? $imgWidth : 72;
                $imgHeight = $this->_calculateDistance($image['height']);
                $imgHeight = $imgHeight ? $imgHeight : 72;
                $imgX      = $this->_calculateDistance($image['x_pos']);
                $imgY      = $this->_calculateDistance($image['y_pos']);
                $page->drawImage($img, $imgX, $imgY, $imgX + $imgWidth, $imgY + $imgHeight);
            }
        }
    }

    public function newPage(array $settings = array())
    {
        $page = parent::newPage($settings);
        if (isset($settings['y'])) {
            $this->y = $settings['y'];
        }
        return $page;
    }

    protected function _setFont($object, $size, $variant = null)
    {
        $font = strtoupper($this->getUseFont());

        switch (strtolower($variant)) {
            case 'bold':
            case 'b':
                $par = 'Bold';
                $std = '_BOLD';
                break;
            case 'italic':
            case 'i':
                $par = 'Italic';
                $std = '_ITALIC';
                break;
            default :
                $par = 'Regular';
                $std = '';
                break;
        }

        $par = '_setFont' . $par;
        if ($font == 'B') {
            return parent::$par($object, $size);
        }
        $font = Zend_Pdf_Font::fontWithName(constant('Zend_Pdf_Font::FONT_' . $font . $std));
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * @param Varien_Object $data
     * @return void
     */
    public function setCertificateData(Varien_Object $data)
    {
        $this->_certificate_data = $data;
    }

    /**
     * @return Varien_Object
     */
    public function getCertificateData()
    {
        if (!isset($this->_certificate_data) && $this->hasData('cert_data')) {
            $this->setCertificateData($this->_getData('cert_data'));
        }
        return $this->_certificate_data;
    }

    /**
     * @return string
     */
    protected function _getPageSize()
    {
        $settings   = $this->getSettings();
        $pageWidth  = $this->_calculateDistance($settings['page_width']);
        $pageHeight = $this->_calculateDistance($settings['page_height']);

        $pageWidth  = !empty($pageWidth) ? $pageWidth : '612';
        $pageHeight = !empty($pageHeight) ? $pageHeight : '198';

        // this is one form that zend pdf accepts for page dimensions
        // Latest versions of Zend_Pdf accept 'x:y' and 'x:y:' form, but older versions
        // used in Magento 1.4.0.1 accept 'x:y:' only
        return $pageWidth . ':' . $pageHeight . ':';
    }

    /**
     * Font to use from settings
     * @return string
     */
    protected function getUseFont()
    {
        if (!isset($this->_use_font)) {
            $settings        = $this->getSettings();
            $this->_use_font = $settings['use_font'];
        }
        return $this->_use_font;
    }

    /**
     * @param Zend_Pdf_Page $page
     * @return Zend_Pdf_Page
     */
    public function insertFields($page)
    {
        $settings      = $this->getSettings();
        $fieldSettings = isset($settings['text_settings']) ? $settings['text_settings'] : array();
        if (!empty($fieldSettings) && !is_array($fieldSettings)) {
            $fieldSettings = unserialize($fieldSettings);
        }
        $gc = $this->getCertificateData()->getData('gc');
        foreach ($fieldSettings as $field) {
            $fSize     = $field['font_size'];
            $fTemplate = $field['template'];
            $fType     = $field['field'];
            $fX        = $this->_calculateDistance($field['x_pos']);
            $fY        = $this->_calculateDistance($field['y_pos']);
            $fVariant  = $field['font_variant'];
            $fColor    = $field['color'];
            $this->_setFont($page, $fSize, $fVariant);
            if ($fType == 'other') {
                $fText = $fTemplate;
            } elseif (isset($field['date']) && $field['date']) { // if field is marked as date filed
                $fValue = $gc->getData($fType);
                $time   = strtotime($fValue); // parse its value to time integer
                if ($time) {
                    $fText = strftime($fTemplate, $time); // and format it according template
                }
            } else if ($fType == 'balance') {
                /* @var $store Mage_Core_Model_Store */
                $store = Mage::app()->getStore($gc->getStore());
                $fText = sprintf($fTemplate, $store->convertPrice($gc->getData($fType), true, false));
            } else {
                $fValue = $gc->getData($fType);
                $fText  = sprintf($fTemplate, $fValue);
            }
            $page->setFillColor($this->_getColor($fColor)); //->drawText($fText, $fX, $fY, 'UTF-8');
            // display multi-line text
            foreach (explode(PHP_EOL, $fText) as $i => $line) {
                $page->drawText($line, $fX, $fY - $i * $page->getFontSize(), 'UTF-8');
            }
        }
        return $page;
    }

    protected function _getWeight($weight)
    {
        switch ($weight) {
            case 'i':
                return 'italic';
                break;
            case 'b':
                return 'bold';
                break;
        }
        return 'regular';
    }

    protected function _getColor($color)
    {
        if (!$color) {
            $color = '000000';
        }
        try {
            $color = new Zend_Pdf_Color_Html('#' . $color);
            return $color;
        } catch (Zend_Pdf_Exception $e) {
            return new Zend_Pdf_Color_Html('#000000');
        }
    }

    protected function _calculateDistance($configDistance)
    {
        $unit      = $this->getUnit();
        $pointsPer = array(
            'in'  => 72,
            'mm'  => 2.83,
            'pts' => 1
        );
        if (array_key_exists($unit, $pointsPer)) {
            return $pointsPer[$unit] * (float)$configDistance;
        }
        return $pointsPer['pts'] * (float)$configDistance;
    }

    /**
     * Return set units of measure
     * @return string
     */
    public function getUnit()
    {
        if (!isset($this->_unit)) {
            $pdfSettings = $this->getSettings();
            $this->_unit = $pdfSettings['units'];
        }
        return $this->_unit;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        if (empty($this->_pdf_settings)) {
            /* @var $gcData Varien_Object */
            $gcData = $this->getCertificateData();
            /* @var $gc Unirgy_Giftcert_Model_Cert */
            $gc          = $gcData->getData('gc');
            $pdfSettings = $this->getStorePdfSettings($gcData->getStore());
            if ($settings = $gc->getPdfSettings()) {
                $gcPdfSettings = $settings;
                if ($gcPdfSettings && !is_array($gcPdfSettings)) {
                    $gcPdfSettings = Zend_Json::decode($gcPdfSettings);
                }
//                unset($gcPdfSettings['use_default_pdf']); // make sure that meta setting is out of the way
                if (!empty($gcPdfSettings)) {
                    $pdfSettings = $gcPdfSettings;
                }
            }
            // default image and text settings are stored as serialized data, so we decode them
            foreach (array('image_settings', 'text_settings') as $key) {
                if (isset($pdfSettings[$key]) && !is_array($pdfSettings[$key])) {
                    $pdfSettings[$key] = Zend_Json::decode($pdfSettings[$key]);
                }
            }
            $this->_pdf_settings = $pdfSettings;
        }

        return $this->_pdf_settings;
    }

    public function getStorePdfSettings($store)
    {
        try {
            $pdfTplId = Mage::getStoreConfig(self::GLOBAL_CONFIG_PDF_TPL, $store);
            if ($pdfTplId) {
                $pdf = Mage::getModel('ugiftcert/pdf_model')->load($pdfTplId);
                if ($pdf->getData('settings')) {
                    $settings = $pdf->getData('settings');
                    if (!is_array($settings)) {
                        $settings = Zend_Json::decode($settings);
                    }
                }
            }
        } catch (Exception $e) {
            // if something goes wrong with new settings, try old ones and add log entry
            Mage::log($e->getMessage(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            $settings = Mage::getStoreConfig(self::GLOBAL_CONFIG_CERT, $store);
        }
        return $settings;
    }
}

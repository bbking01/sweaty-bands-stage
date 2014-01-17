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
class Unirgy_Giftcert_Block_Product_Type extends Mage_Catalog_Block_Product_View_Abstract
{
    /**
     * @var Mage_Directory_Model_Currency
     */
    public $_currency;

    /**
     * @var array
     */
    public $_amountConfig;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (Mage::getStoreConfig('ugiftcert/custom/message_preview')) {
            $media = $this->getLayout()->getBlock('product.info.media');
            if ($media) {
                $media->setTemplate('unirgy/giftcert/product/media.phtml');
            }
        }
    }

    /**
     * @deprecated since 0.8.2 for getAmountConfig()
     */
    public function getAmountRangeFrom($fromAttr = 'giftcert_amount_from')
    {
        $from = $this->getProduct()->getDataUsingMethod($fromAttr);
        if (!$from) {
            $from = Mage::getStoreConfig('ugiftcert/custom/amount_from');
        }
        return $from;
    }

    /**
     * @deprecated since 0.8.2 for getAmountConfig()
     */
    public function getAmountRangeTo($toAttr = 'giftcert_amount_to')
    {
        $to = $this->getProduct()->getDataUsingMethod($toAttr);
        if (!$to) {
            $to = Mage::getStoreConfig('ugiftcert/custom/amount_to');
        }
        return $to;
    }

    /**
     * @param string $attr
     *
     * @return array
     */
    public function getAmountConfig($attr = 'ugiftcert_amount_config')
    {
        if (!$this->_amountConfig) {
            $_amountConfig = Mage::helper('ugiftcert')->getAmountConfig($this->getProduct(), $attr);
            /* @var $buy_req Varien_Object */
            $buy_req = $this->getBuyRequest();
            if ($buy_req) {
                $currentAmount = $buy_req->getData('amount');
                $recType       = $buy_req->getData('recipient_type');
                $recName       = $buy_req->getData('recipient_name');
                $recEmail      = $buy_req->getData('recipient_email');
                $recMsg        = $buy_req->getData('recipient_message');
                $recAddr       = $buy_req->getData('recipient_address');
                $sendOn        = $buy_req->getData('send_on');
                $data          = $buy_req->getData();
                unset($data['amount'], $data['options']);

                $_amountConfig = array_merge(
                    $_amountConfig,
                    compact('currentAmount', 'recType', 'recName', 'recEmail', 'recMsg', 'recAddr', 'sendOn'),
                    $data
                );
            }
            $this->_amountConfig = $_amountConfig;
        }
        return $this->_amountConfig;

    }

    public function getAllowVirtual()
    {
        return Mage::getStoreConfig('ugiftcert/email/enabled');
    }

    public function getAllowRecipientEmail()
    {
        return Mage::getStoreConfig('ugiftcert/email/allow_recipient_email');
    }

    public function getAllowPhysical()
    {
        return Mage::getStoreConfig('ugiftcert/address/enabled');
    }

    public function getAllowMessage()
    {
        return Mage::getStoreConfig('ugiftcert/custom/allow_message');
    }

    public function getMessageMaxLength()
    {
        $len = Mage::getStoreConfig('ugiftcert/custom/message_max_length');
        return $len;
    }

    public function getBuyRequest()
    {
        $_product = $this->getProduct();
        $buy_req  = null;
        if ($_product->getConfigureMode()) {
            $_quote = Mage::getSingleton('checkout/cart')->getQuote();
            $_item  = $_quote->getItemById($this->getRequest()->getParam('id'));
            if ($_item) {
                $buy_req = $_item->getBuyRequest();
            }
        }
        return $buy_req;
    }

    public function getDatePicker()
    {
        $form = new Varien_Data_Form();

        $element = new Varien_Data_Form_Element_Date(array(
                                                          'name'         => 'send_on',
                                                          'html_id'      => 'send_on',
                                                          'label'        => $this->__('Schedule sending on:'),
                                                          'title'        => $this->__('Schedule sending on'),
                                                          'image'        => $this->getSkinUrl('images/grid-cal.gif'),
                                                          'format'       => Mage::app()->getLocale()
                                                                            ->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                                                          'input_format' => Varien_Date::DATE_INTERNAL_FORMAT
                                                     ));
        if ($buy_req = $this->getBuyRequest()) {
            $element->setValue($buy_req->getData('send_on'));
        }

        $form->addElement($element);
        $html = '<label for="' . $element->getHtmlId() . '">' . $element->getLabel()
                . ($element->getRequired() ? ' <span class="required">*</span>' : '') . '</label><br>' . "\n";
        return $html . $element->getElementHtml();
    }

    public function getAllowSchduled()
    {
        return Mage::getStoreConfig("ugiftcert/email/allow_scheduled_sending");
    }

    /**
     * @param array $config
     * @return string
     */
    public function getAmountHtml($config)
    {
        if (!isset($config['type'])) {
            Mage::throwException($this->__("Incorrect configuration used."));
        }

        $type = $config['type'];
        switch ($type) {
            case 'range':
                $html = $this->_getRangeAmountHtml($config);
                break;
            case 'fixed':
                $html = $this->_getFixedAmountHtml($config);
                break;
            case 'dropdown':
                $html = $this->_getDropDownAmountHtml($config);
                break;
            default :
                $html = $this->_getAnyAmountHtml($config);
                break;
        }
        return $html;
    }

    /**
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrency()
    {
        if (!isset($this->_currency)) {
            $this->_currency = Mage::app()->getStore()->getCurrentCurrency();
        }
        return $this->_currency;
    }

    /**
     * @param $config
     *
     * @return string
     */
    protected function _getRangeAmountHtml($config)
    {
        $html = '';
        if (isset($config['from'], $config['to'])) {
            $_from     = $config['from'];
            $_to       = $config['to'];
            $_currency = $this->getCurrency();
            $_class    = ' validate-number-range from-' . $_currency->convert($_from) . '-to-' . $_currency->convert($_to);
            $value     = isset($config['currentAmount']) ? 'value="' . $config['currentAmount'] . '"' : '';
            $html      = sprintf('<label for="amount">%s</label><br/>', $this->__('Enter Amount:'));
            $html .= str_replace('%s', '', $_currency->getOutputFormat());
            $html .= sprintf('&nbsp;<input type="text" id="amount" name="amount" class="input-text required-entry validate-number%s"%s/>&nbsp;', $_class, $value);
            $html .= sprintf('(%s  - %s )', $_currency->format($_from), $_currency->format($_to));
        }
        return $html;
    }

    protected function _getDropDownAmountHtml($config)
    {

        $label = $this->__('Select Amount:');
        $html  = sprintf('<label for="amount">%s</label><br/>
            <select id="amount" name="amount" class="select required-entry">', $label);

        foreach ($config['options'] as $_value):
            $selected    = isset($config['currentAmount']) && $config['currentAmount'] == $_value ? 'selected="selected"' : '';
            $_valueLabel = !$_value ? $this->__('Please select') : $this->getCurrency()->format($_value);
            $html .= sprintf('<option value="%s" %s>%s</option>', $_value, $selected, $_valueLabel);
        endforeach;

        $html .= '</select>';
        return $html;
    }

    protected function _getFixedAmountHtml($config)
    {
        $label = $this->__('Amount: %s', $this->getCurrency()->format($config['amount']));
        $value = $config['amount'];
        $html  = sprintf('<label>%s</label><input type="hidden" name="amount" value="%s"/>', $label, $value);
        return $html;
    }

    protected function _getAnyAmountHtml($config)
    {
        $label  = $this->__('Enter Amount:');
        $symbol = str_replace('%s', '', $this->getCurrency()->getOutputFormat());
        $value  = isset($config['currentAmount']) ? 'value="' . $config['currentAmount'] . '"' : '';
        $html   = sprintf('<label for="amount">%s</label><br/>%s
            <input type="text" id="amount" name="amount" class="input-text required-entry validate-number"%s/>', $label, $symbol, $value);
        return $html;
    }

    public function getProductPersonalizationOptions()
    {
        $product = $this->getProduct();
        $data    = $product->getData('ugiftcert_personalization');
        $result  = array();
        if (!$data) {
            return $result;
        }
        if (!is_array($data)) {
            $data = Zend_Json::decode($data);
        }

        foreach ($data as $option) {
            $email        = empty($option['email_template']) ? 0 : $option['email_template'];
            $pdf          = empty($option['pdf_template']) ? 0 : $option['pdf_template'];
            $key          = $email . ':' . $pdf;
            $result[$key] = $option;
        }

        return $result;
    }
}
